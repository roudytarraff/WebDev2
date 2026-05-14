<?php

namespace App\Http\Controllers\Office;

use App\Models\DocumentType;
use App\Models\Service;
use App\Models\ServiceRequiredDocument;
use Illuminate\Http\Request;

class OfficeRequiredDocumentController extends OfficeBaseController
{
    public function create(string $serviceId)
    {
        $office = $this->currentOffice();
        $service = Service::where('office_id', $office->id)->findOrFail($serviceId);
        $documentTypes = DocumentType::where('office_id', $office->id)->where('status', 'active')->orderBy('name')->get();

        return view('office.required_documents.create', compact('service', 'documentTypes'));
    }

    public function store(Request $request, string $serviceId)
    {
        $request->validate([
            'document_type_id' => 'required|exists:document_types,id',
            'is_required' => 'nullable|boolean',
        ]);

        $office = $this->currentOffice();
        $service = Service::where('office_id', $office->id)->findOrFail($serviceId);
        $documentType = DocumentType::where('office_id', $office->id)->findOrFail($request->document_type_id);

        $document = new ServiceRequiredDocument();
        $document->service_id = $service->id;
        $document->document_type_id = $documentType->id;
        $document->document_name = $documentType->name;
        $document->is_required = $request->boolean('is_required');
        $document->save();

        return redirect()->route('office.services.show', $service->id)->with('success', 'Required document added successfully.');
    }

    public function edit(string $id)
    {
        $document = $this->officeDocument($id);
        $office = $this->currentOffice();
        $documentTypes = DocumentType::where('office_id', $office->id)->where('status', 'active')->orderBy('name')->get();

        return view('office.required_documents.edit', compact('document', 'documentTypes'));
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'document_type_id' => 'required|exists:document_types,id',
            'is_required' => 'nullable|boolean',
        ]);

        $document = $this->officeDocument($id);
        $office = $this->currentOffice();
        $documentType = DocumentType::where('office_id', $office->id)->findOrFail($request->document_type_id);

        $document->document_type_id = $documentType->id;
        $document->document_name = $documentType->name;
        $document->is_required = $request->boolean('is_required');
        $document->save();

        return redirect()->route('office.services.show', $document->service_id)->with('success', 'Required document updated successfully.');
    }

    public function destroy(string $id)
    {
        $document = $this->officeDocument($id);
        $serviceId = $document->service_id;
        $document->delete();

        return redirect()->route('office.services.show', $serviceId)->with('success', 'Required document deleted successfully.');
    }

    private function officeDocument(string $id): ServiceRequiredDocument
    {
        $office = $this->currentOffice();

        return ServiceRequiredDocument::whereHas('service', function ($query) use ($office) {
            $query->where('office_id', $office->id);
        })->findOrFail($id);
    }
}
