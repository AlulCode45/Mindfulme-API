<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BundlePackageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $bundleId = $this->route('bundle_package');
        return [
            'package_name' => $this->isMethod('post')
                ? 'required|string|max:255|unique:bundle_packages,package_name'
                : 'required|string|max:255|unique:bundle_packages,package_name,' . $bundleId . ',bundle_id',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'points' => 'required|integer|min:0',
        ];
    }
}
