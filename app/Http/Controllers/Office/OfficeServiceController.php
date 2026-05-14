<?php

namespace App\Http\Controllers\Office;

use App\Models\DocumentType;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OfficeServiceController extends OfficeBaseController
{
    public function index()
    {
        $office = $this->currentOffice();
        $services = Service::with(['category', 'documents'])
            ->where('office_id', $office->id)
            ->orderBy('name')
            ->get();

        return view('office.services.index', compact('office', 'services'));
    }

    public function create()
    {
        $office = $this->currentOffice();
        $categories = ServiceCategory::where('office_id', $office->id)->orderBy('name')->get();
        $documentTypes = DocumentType::where('office_id', $office->id)->where('status', 'active')->orderBy('name')->get();

        return view('office.services.create', compact('categories', 'documentTypes'));
    }

    public function store(Request $request)
    {
        $request->validate($this->rules());

        $office = $this->currentOffice();
        $this->checkCategory($office->id, $request->category_id);

        $service = new Service();
        $service->office_id = $office->id;
        $this->fillService($service, $request);
        $service->save();
        $this->storeDocumentNames($service, $request);

        return redirect()->route('office.services.index')->with('success', 'Service created successfully.');
    }

    public function show(string $id)
    {
        $office = $this->currentOffice();
        $service = Service::with(['category', 'documents'])
            ->where('office_id', $office->id)
            ->findOrFail($id);

        return view('office.services.show', compact('service'));
    }

    public function edit(string $id)
    {
        $office = $this->currentOffice();
        $service = Service::where('office_id', $office->id)->findOrFail($id);
        $categories = ServiceCategory::where('office_id', $office->id)->orderBy('name')->get();
        $documentTypes = DocumentType::where('office_id', $office->id)->where('status', 'active')->orderBy('name')->get();

        return view('office.services.edit', compact('service', 'categories', 'documentTypes'));
    }

    public function update(Request $request, string $id)
    {
        $request->validate($this->rules());

        $office = $this->currentOffice();
        $this->checkCategory($office->id, $request->category_id);

        $service = Service::where('office_id', $office->id)->findOrFail($id);
        $this->fillService($service, $request);
        $service->save();
        $this->storeDocumentNames($service, $request);

        return redirect()->route('office.services.index')->with('success', 'Service updated successfully.');
    }

    public function destroy(string $id)
    {
        $office = $this->currentOffice();
        $service = Service::where('office_id', $office->id)->findOrFail($id);
        $service->delete();

        return redirect()->route('office.services.index')->with('success', 'Service deleted successfully.');
    }

    private function rules(): array
    {
        return [
            'category_id' => 'required|exists:service_categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'instructions' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'duration_minutes' => 'required|integer|min:1',
            'requires_appointment' => 'nullable|boolean',
            'supports_online_payment' => 'nullable|boolean',
            'supports_crypto_payment' => 'nullable|boolean',
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'document_type_ids' => 'nullable|array',
            'document_type_ids.*' => 'exists:document_types,id',
        ];
    }

    private function fillService(Service $service, Request $request): void
    {
        $service->category_id = $request->category_id;
        $service->name = $request->name;
        $service->description = $request->description;
        $service->instructions = $request->instructions;
        $service->price = $request->price;
        $service->duration_minutes = $request->duration_minutes;
        $service->requires_appointment = $request->boolean('requires_appointment');
        $service->supports_online_payment = $request->boolean('supports_online_payment');
        $service->supports_crypto_payment = $request->boolean('supports_crypto_payment');
        $service->status = $request->status;
    }

    private function checkCategory(int $officeId, int $categoryId): void
    {
        ServiceCategory::where('office_id', $officeId)->findOrFail($categoryId);
    }

    private function storeDocumentNames(Service $service, Request $request): void
    {
        if (! $request->filled('document_type_ids')) {
            return;
        }

        $officeId = $service->office_id;
        $documentTypes = DocumentType::where('office_id', $officeId)
            ->whereIn('id', $request->document_type_ids)
            ->get();

        foreach ($documentTypes as $documentType) {
            $exists = $service->documents()->where('document_type_id', $documentType->id)->exists();

            if ($exists) {
                continue;
            }

            $service->documents()->create([
                'document_type_id' => $documentType->id,
                'document_name' => $documentType->name,
                'is_required' => true,
            ]);
        }
    }
}
