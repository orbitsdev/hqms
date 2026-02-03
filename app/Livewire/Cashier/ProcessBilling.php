<?php

namespace App\Livewire\Cashier;

use App\Models\BillingItem;
use App\Models\BillingTransaction;
use App\Models\HospitalDrug;
use App\Models\MedicalRecord;
use App\Models\Service;
use App\Notifications\GenericNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

class ProcessBilling extends Component
{
    #[Locked]
    public int $medicalRecordId;

    // Billing items (stored temporarily before saving)
    public array $billingItems = [];

    // Doctor fee override
    public ?float $doctorFeeOverride = null;

    // Items added by cashier (on top of override if set)
    public array $additionalItems = [];

    // Add item modal
    public bool $showAddItemModal = false;

    public string $itemType = 'service';

    public ?int $serviceId = null;

    public ?int $drugId = null;

    public string $customDescription = '';

    public int $itemQuantity = 1;

    public float $itemUnitPrice = 0;

    // Discount
    public string $discountType = 'none';

    public float $discountPercent = 0;

    public string $discountReason = '';

    // Emergency/special charges
    public bool $isEmergency = false;

    public bool $isHoliday = false;

    public bool $isSunday = false;

    public bool $isAfter5pm = false;

    public float $emergencyFee = 0;

    // Payment
    public bool $showPaymentModal = false;

    public string $paymentMethod = 'cash';

    #[Validate('required|numeric|min:0')]
    public float $amountTendered = 0;

    public string $paymentNotes = '';

    // Completed transaction (used for redirect after payment)
    public ?BillingTransaction $completedTransaction = null;

    public function mount(MedicalRecord $medicalRecord): void
    {
        if ($medicalRecord->status !== 'for_billing') {
            Toaster::error(__('This record is not ready for billing.'));
            $this->redirect(route('cashier.queue'), navigate: true);

            return;
        }

        $this->medicalRecordId = $medicalRecord->id;

        // Pre-populate discount from doctor's suggestion
        $this->discountType = $medicalRecord->suggested_discount_type ?? 'none';
        $this->discountReason = $medicalRecord->suggested_discount_reason ?? '';
        $this->setDiscountPercent();

        // Load doctor's fee override if set
        $this->doctorFeeOverride = $medicalRecord->doctor_fee_override;

        // Check if it's after 5pm, Sunday, or holiday
        $now = now();
        $this->isAfter5pm = $now->hour >= 17;
        $this->isSunday = $now->isSunday();

        // Auto-load billing items
        $this->loadInitialItems();
    }

    protected function loadInitialItems(): void
    {
        $record = $this->medicalRecord;

        // If doctor set a fee override, use that as the single base item
        if ($this->doctorFeeOverride !== null) {
            $this->billingItems[] = [
                'type' => 'doctor_override',
                'description' => __('Doctor Fee Override (includes consultation & prescribed items)'),
                'service_id' => null,
                'drug_id' => null,
                'quantity' => 1,
                'unit_price' => $this->doctorFeeOverride,
                'total_price' => $this->doctorFeeOverride,
                'is_override' => true,
            ];

            return;
        }

        // Standard billing: Add professional fee based on consultation type
        $consultationType = $record->consultationType;
        $feeServiceName = match ($consultationType->code ?? '') {
            'ob' => 'Professional Fee - OB',
            'pedia' => 'Professional Fee - Pediatrics',
            default => 'Professional Fee - General',
        };

        $consultationService = Service::where('service_name', $feeServiceName)
            ->where('is_active', true)
            ->first();

        // Fallback to any consultation service if specific one not found
        if (! $consultationService) {
            $consultationService = Service::where('category', 'consultation')
                ->where('service_name', 'like', 'Professional Fee%')
                ->where('is_active', true)
                ->first();
        }

        if ($consultationService) {
            $this->billingItems[] = [
                'type' => 'professional_fee',
                'description' => $consultationService->service_name.' ('.$consultationType->name.')',
                'service_id' => $consultationService->id,
                'drug_id' => null,
                'quantity' => 1,
                'unit_price' => (float) $consultationService->base_price,
                'total_price' => (float) $consultationService->base_price,
            ];
        }

        // Add hospital drugs from prescriptions
        $prescriptions = $record->prescriptions()
            ->where('is_hospital_drug', true)
            ->with('hospitalDrug')
            ->get();

        foreach ($prescriptions as $prescription) {
            if ($prescription->hospitalDrug) {
                $qty = $prescription->quantity ?? 1;
                $unitPrice = (float) $prescription->hospitalDrug->unit_price;

                $this->billingItems[] = [
                    'type' => 'drug',
                    'description' => $prescription->hospitalDrug->drug_name.
                        ($prescription->dosage ? ' ('.$prescription->dosage.')' : ''),
                    'service_id' => null,
                    'drug_id' => $prescription->hospital_drug_id,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'total_price' => $qty * $unitPrice,
                ];
            }
        }
    }

    #[Computed]
    public function medicalRecord(): MedicalRecord
    {
        return MedicalRecord::with([
            'consultationType',
            'doctor',
            'prescriptions.hospitalDrug',
            'user',
        ])->findOrFail($this->medicalRecordId);
    }

    #[Computed]
    public function services(): \Illuminate\Database\Eloquent\Collection
    {
        return Service::where('is_active', true)
            ->orderBy('category')
            ->orderBy('display_order')
            ->get();
    }

    #[Computed]
    public function hospitalDrugs(): \Illuminate\Database\Eloquent\Collection
    {
        return HospitalDrug::where('is_active', true)
            ->orderBy('drug_name')
            ->get();
    }

    #[Computed]
    public function subtotal(): float
    {
        return collect($this->billingItems)->sum('total_price');
    }

    #[Computed]
    public function totalEmergencyFee(): float
    {
        $fee = 0;
        if ($this->isEmergency) {
            $fee += 200;
        }
        if ($this->isHoliday) {
            $fee += 150;
        }
        if ($this->isSunday) {
            $fee += 100;
        }
        if ($this->isAfter5pm) {
            $fee += 50;
        }

        return $fee;
    }

    #[Computed]
    public function discountAmount(): float
    {
        return ($this->subtotal + $this->totalEmergencyFee) * ($this->discountPercent / 100);
    }

    #[Computed]
    public function totalAmount(): float
    {
        return $this->subtotal + $this->totalEmergencyFee - $this->discountAmount;
    }

    #[Computed]
    public function change(): float
    {
        return max(0, $this->amountTendered - $this->totalAmount);
    }

    public function updatedDiscountType(): void
    {
        $this->setDiscountPercent();
    }

    protected function setDiscountPercent(): void
    {
        $this->discountPercent = match ($this->discountType) {
            'senior', 'pwd' => 20,
            'employee' => 50,
            'family' => 10,
            default => 0,
        };
    }

    // Add Item Modal
    public function openAddItemModal(): void
    {
        $this->resetItemForm();
        $this->showAddItemModal = true;
    }

    public function closeAddItemModal(): void
    {
        $this->showAddItemModal = false;
        $this->resetItemForm();
    }

    protected function resetItemForm(): void
    {
        $this->itemType = 'service';
        $this->serviceId = null;
        $this->drugId = null;
        $this->customDescription = '';
        $this->itemQuantity = 1;
        $this->itemUnitPrice = 0;
    }

    public function updatedServiceId(): void
    {
        if ($this->serviceId) {
            $service = Service::find($this->serviceId);
            if ($service) {
                $this->itemUnitPrice = (float) $service->base_price;
            }
        }
    }

    public function updatedDrugId(): void
    {
        if ($this->drugId) {
            $drug = HospitalDrug::find($this->drugId);
            if ($drug) {
                $this->itemUnitPrice = (float) $drug->unit_price;
            }
        }
    }

    public function addItem(): void
    {
        $this->validate([
            'itemType' => 'required|in:service,drug,procedure,other',
            'itemQuantity' => 'required|integer|min:1',
            'itemUnitPrice' => 'required|numeric|min:0',
        ]);

        $description = '';
        $serviceId = null;
        $drugId = null;

        if ($this->itemType === 'service' && $this->serviceId) {
            $service = Service::find($this->serviceId);
            $description = $service?->service_name ?? 'Service';
            $serviceId = $this->serviceId;
        } elseif ($this->itemType === 'drug' && $this->drugId) {
            $drug = HospitalDrug::find($this->drugId);
            $description = $drug?->drug_name ?? 'Drug';
            $drugId = $this->drugId;
        } else {
            if (empty($this->customDescription)) {
                $this->addError('customDescription', 'Description is required');

                return;
            }
            $description = $this->customDescription;
        }

        $this->billingItems[] = [
            'type' => $this->itemType,
            'description' => $description,
            'service_id' => $serviceId,
            'drug_id' => $drugId,
            'quantity' => $this->itemQuantity,
            'unit_price' => $this->itemUnitPrice,
            'total_price' => $this->itemQuantity * $this->itemUnitPrice,
        ];

        $this->closeAddItemModal();
        Toaster::success(__('Item added.'));
    }

    public function removeItem(int $index): void
    {
        if (isset($this->billingItems[$index])) {
            // Prevent removal of doctor override item
            if ($this->billingItems[$index]['is_override'] ?? false) {
                Toaster::error(__('Cannot remove doctor fee override.'));

                return;
            }

            unset($this->billingItems[$index]);
            $this->billingItems = array_values($this->billingItems);
            Toaster::info(__('Item removed.'));
        }
    }

    public function updateItemQuantity(int $index, int $quantity): void
    {
        if (isset($this->billingItems[$index]) && $quantity > 0) {
            // Prevent changing quantity of doctor override item
            if ($this->billingItems[$index]['is_override'] ?? false) {
                return;
            }

            $this->billingItems[$index]['quantity'] = $quantity;
            $this->billingItems[$index]['total_price'] = $quantity * $this->billingItems[$index]['unit_price'];
        }
    }

    // Payment Modal
    public function openPaymentModal(): void
    {
        if (empty($this->billingItems)) {
            Toaster::error(__('Please add at least one billing item.'));

            return;
        }

        $this->amountTendered = $this->totalAmount;
        $this->showPaymentModal = true;
    }

    public function closePaymentModal(): void
    {
        $this->showPaymentModal = false;
    }

    public function processPayment(): void
    {
        $rules = [
            'paymentMethod' => 'required|in:cash,gcash,card,bank_transfer,philhealth',
            'amountTendered' => 'required|numeric|min:'.$this->totalAmount,
        ];

        // Require reason for discounts (audit trail)
        if ($this->discountType !== 'none') {
            $rules['discountReason'] = 'required|string|min:3';
        }

        // Require valid percentage for "other" discount
        if ($this->discountType === 'other') {
            $rules['discountPercent'] = 'required|numeric|min:0|max:100';
        }

        $this->validate($rules, [
            'discountReason.required' => __('Please provide a reason for the discount (e.g., ID number or approval details).'),
            'discountPercent.required' => __('Please enter a discount percentage.'),
            'discountPercent.max' => __('Discount cannot exceed 100%.'),
        ]);

        DB::transaction(function (): void {
            // Generate transaction number
            $transactionNumber = 'TXN-'.date('Ymd').'-'.str_pad(
                BillingTransaction::whereDate('created_at', today())->count() + 1,
                4,
                '0',
                STR_PAD_LEFT
            );

            // Create billing transaction
            $transaction = BillingTransaction::create([
                'user_id' => $this->medicalRecord->user_id,
                'medical_record_id' => $this->medicalRecordId,
                'transaction_number' => $transactionNumber,
                'transaction_date' => today(),
                'is_emergency' => $this->isEmergency,
                'is_holiday' => $this->isHoliday,
                'is_sunday' => $this->isSunday,
                'is_after_5pm' => $this->isAfter5pm,
                'emergency_fee' => $this->totalEmergencyFee,
                'subtotal' => $this->subtotal,
                'discount_type' => $this->discountType,
                'discount_amount' => $this->discountAmount,
                'discount_reason' => $this->discountReason ?: null,
                'total_amount' => $this->totalAmount,
                'payment_status' => 'paid',
                'amount_paid' => $this->amountTendered,
                'balance' => 0,
                'payment_method' => $this->paymentMethod,
                'received_in_billing_at' => now(),
                'ended_in_billing_at' => now(),
                'processed_by' => Auth::id(),
                'notes' => $this->paymentNotes ?: null,
            ]);

            // Create billing items
            foreach ($this->billingItems as $item) {
                BillingItem::create([
                    'billing_transaction_id' => $transaction->id,
                    'item_type' => $item['type'],
                    'item_description' => $item['description'],
                    'service_id' => $item['service_id'],
                    'hospital_drug_id' => $item['drug_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['total_price'],
                ]);
            }

            // Update medical record status
            $this->medicalRecord->update(['status' => 'completed']);

            // Notify patient
            $patientUser = $this->medicalRecord->user;
            if ($patientUser && $patientUser->hasRole('patient')) {
                $patientUser->notify(new GenericNotification([
                    'type' => 'billing.completed',
                    'title' => __('Payment Completed'),
                    'message' => __('Your payment of ₱:amount has been processed. Thank you!', [
                        'amount' => number_format($this->totalAmount, 2),
                    ]),
                    'medical_record_id' => $this->medicalRecordId,
                    'transaction_id' => $transaction->id,
                    'sender_id' => Auth::id(),
                    'sender_role' => 'cashier',
                ]));
            }

            $this->completedTransaction = $transaction;
        });

        $this->closePaymentModal();

        Toaster::success(__('Payment processed successfully.'));

        // Redirect to transaction details page
        $this->redirect(route('cashier.transaction', $this->completedTransaction), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.cashier.process-billing')
            ->layout('layouts.app');
    }
}
