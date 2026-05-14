<?php

namespace App\Http\Controllers\Office;

use App\Models\DocumentType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OfficeDocumentTypeController extends OfficeBaseController
{
    public function index()
    {
        $office = $this->currentOffice();
        $documentTypes = DocumentType::where('office_id', $office->id)->orderBy('name')->get();

        return view('office.document_types.index', compact('office', 'documentTypes'));
    }

    public function create()
    {
        return view('office.document_types.create');
    }

    public function store(Request $request)
    {
        $request->validate($this->rules());

        $office = $this->currentOffice();

        $documentType = new DocumentType();
        $documentType->office_id = $office->id;
        $documentType->name = $request->name;
        $documentType->description = $request->description;
        $documentType->status = $request->status;
        $documentType->save();

        return redirect()->route('office.document-types.index')->with('success', 'Document type created successfully.');
    }

    public function edit(string $id)
    {
        $documentType = $this->officeDocumentType($id);

        return view('office.document_types.edit', compact('documentType'));
    }

    public function update(Request $request, string $id)
    {
        $request->validate($this->rules());

        $documentType = $this->officeDocumentType($id);
        $documentType->name = $request->name;
        $documentType->description = $request->description;
        $documentType->status = $request->status;
        $documentType->save();

        return redirect()->route('office.document-types.index')->with('success', 'Document type updated successfully.');
    }

    public function destroy(string $id)
    {
        $documentType = $this->officeDocumentType($id);
        $documentType->delete();

        return redirect()->route('office.document-types.index')->with('success', 'Document type deleted successfully.');
    }

    private function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }

    private function officeDocumentType(string $id): DocumentType
    {
        $office = $this->currentOffice();

        return DocumentType::where('office_id', $office->id)->findOrFail($id);
    }
}
