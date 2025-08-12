import React, { useState, useEffect } from "react";
import {
    Building2,
    ChevronDown,
    Loader2,
    AlertCircle,
    CheckCircle,
} from "lucide-react";

const UnitOrganisasiComponent = ({
    data,
    setData,
    errors = {},
    required = false,
}) => {
    const [unitOrganisasiOptions, setUnitOrganisasiOptions] = useState([]);
    const [unitOptions, setUnitOptions] = useState([]);
    const [subUnitOptions, setSubUnitOptions] = useState([]);
    const [loading, setLoading] = useState({
        unitOrganisasi: false,
        unit: false,
        subUnit: false,
    });
    const [apiErrors, setApiErrors] = useState({
        unit: null,
        subUnit: null,
    });

    // Unit Organisasi options (static dari backend)
    const staticUnitOrganisasi = [
        "EGM",
        "GM",
        "Airside",
        "Landside",
        "Back Office",
        "SSQC",
        "Ancillary",
    ];

    // Initialize unit organisasi options
    useEffect(() => {
        setUnitOrganisasiOptions(staticUnitOrganisasi);
    }, []);

    // Load units ketika unit organisasi dipilih
    useEffect(() => {
        if (data.unit_organisasi) {
            loadUnits(data.unit_organisasi);
        } else {
            resetDependentFields();
        }
    }, [data.unit_organisasi]);

    // Load sub units ketika unit dipilih
    useEffect(() => {
        if (data.unit_id) {
            loadSubUnits(data.unit_id);
        } else {
            setSubUnitOptions([]);
            setApiErrors((prev) => ({ ...prev, subUnit: null }));
            if (typeof setData === "function") {
                setData("sub_unit_id", "");
            }
        }
    }, [data.unit_id]);

    const resetDependentFields = () => {
        setUnitOptions([]);
        setSubUnitOptions([]);
        setApiErrors({ unit: null, subUnit: null });
        if (typeof setData === "function") {
            setData("unit_id", "");
            setData("sub_unit_id", "");
        }
    };

    const loadUnits = async (unitOrganisasi) => {
        setLoading((prev) => ({ ...prev, unit: true }));
        setApiErrors((prev) => ({ ...prev, unit: null }));

        try {
            const response = await fetch(
                `/api/units?unit_organisasi=${encodeURIComponent(
                    unitOrganisasi
                )}`,
                {
                    method: "GET",
                    headers: {
                        "Content-Type": "application/json",
                        Accept: "application/json",
                        "X-Requested-With": "XMLHttpRequest",
                    },
                }
            );

            if (!response.ok) {
                throw new Error(
                    `HTTP ${response.status}: ${response.statusText}`
                );
            }

            const result = await response.json();

            if (result.success && Array.isArray(result.data)) {
                setUnitOptions(result.data);

                if (result.data.length === 0) {
                    setApiErrors((prev) => ({
                        ...prev,
                        unit: `Tidak ada unit ditemukan untuk ${unitOrganisasi}. Pastikan UnitSeeder sudah dijalankan.`,
                    }));
                }
            } else {
                setUnitOptions([]);
                const errorMessage =
                    result.message || "Invalid response format from server";
                setApiErrors((prev) => ({ ...prev, unit: errorMessage }));
            }
        } catch (error) {
            setUnitOptions([]);

            let errorMessage = "Error loading units";
            if (error.message.includes("fetch")) {
                errorMessage =
                    "Koneksi ke server gagal. Pastikan server Laravel berjalan.";
            } else if (error.message.includes("404")) {
                errorMessage =
                    "API endpoint tidak ditemukan. Pastikan routes sudah dikonfigurasi.";
            } else if (error.message.includes("500")) {
                errorMessage =
                    "Server error. Pastikan UnitSeeder sudah dijalankan dan database tersambung.";
            } else {
                errorMessage = `Error: ${error.message}`;
            }

            setApiErrors((prev) => ({ ...prev, unit: errorMessage }));
        } finally {
            setLoading((prev) => ({ ...prev, unit: false }));
        }
    };

    const loadSubUnits = async (unitId) => {
        setLoading((prev) => ({ ...prev, subUnit: true }));
        setApiErrors((prev) => ({ ...prev, subUnit: null }));

        try {
            const response = await fetch(
                `/api/sub-units?unit_id=${encodeURIComponent(unitId)}`,
                {
                    method: "GET",
                    headers: {
                        "Content-Type": "application/json",
                        Accept: "application/json",
                        "X-Requested-With": "XMLHttpRequest",
                    },
                }
            );

            if (!response.ok) {
                throw new Error(
                    `HTTP ${response.status}: ${response.statusText}`
                );
            }

            const result = await response.json();

            if (result.success && Array.isArray(result.data)) {
                setSubUnitOptions(result.data);
            } else {
                setSubUnitOptions([]);
                const errorMessage =
                    result.message || "Invalid response format from server";
                setApiErrors((prev) => ({ ...prev, subUnit: errorMessage }));
            }
        } catch (error) {
            setSubUnitOptions([]);

            let errorMessage = "Error loading sub units";
            if (error.message.includes("fetch")) {
                errorMessage = "Koneksi ke server gagal.";
            } else if (error.message.includes("404")) {
                errorMessage =
                    "Unit tidak ditemukan atau sub unit API tidak tersedia.";
            } else if (error.message.includes("500")) {
                errorMessage = "Server error saat memuat sub units.";
            } else {
                errorMessage = `Error: ${error.message}`;
            }

            setApiErrors((prev) => ({ ...prev, subUnit: errorMessage }));
        } finally {
            setLoading((prev) => ({ ...prev, subUnit: false }));
        }
    };

    const handleUnitOrganisasiChange = (value) => {
        if (typeof setData === "function") {
            setData("unit_organisasi", value);
            setData("unit_id", "");
            setData("sub_unit_id", "");
        }

        resetDependentFields();
    };

    const handleUnitChange = (value) => {
        if (typeof setData === "function") {
            setData("unit_id", value);
            setData("sub_unit_id", "");
        }

        setSubUnitOptions([]);
        setApiErrors((prev) => ({ ...prev, subUnit: null }));
    };

    const handleSubUnitChange = (value) => {
        if (typeof setData === "function") {
            setData("sub_unit_id", value);
        }
    };

    const DropdownField = ({
        label,
        name,
        value,
        options,
        onChange,
        loading = false,
        error,
        apiError,
        placeholder,
        disabled = false,
        required = true,
    }) => {
        const [focused, setFocused] = useState(false);

        return (
            <div className="space-y-2">
                <label className="flex items-center gap-2 text-sm font-semibold text-gray-700">
                    <Building2
                        className={`w-4 h-4 transition-colors duration-200 ${
                            focused || value
                                ? "text-[#439454]"
                                : "text-gray-400"
                        }`}
                    />
                    {label}
                    {required && <span className="text-red-500">*</span>}
                </label>

                <div className="relative">
                    <select
                        name={name}
                        value={value || ""}
                        onChange={(e) => {
                            const selectedValue = e.target.value;
                            onChange(selectedValue);
                        }}
                        onFocus={() => setFocused(true)}
                        onBlur={() => setFocused(false)}
                        disabled={disabled || loading}
                        className={`w-full px-4 py-3 pr-10 text-gray-900 transition-all duration-300 border-2 rounded-xl focus:ring-4 focus:ring-[#439454]/20 focus:border-[#439454] hover:border-[#439454]/60 disabled:opacity-50 disabled:cursor-not-allowed appearance-none ${
                            error || apiError
                                ? "border-red-300 bg-red-50"
                                : focused
                                ? "border-[#439454] bg-white shadow-lg"
                                : "border-gray-300 bg-white"
                        }`}
                    >
                        <option value="">{placeholder}</option>
                        {Array.isArray(options) &&
                            options.map((option, index) => {
                                const optionValue =
                                    typeof option === "object"
                                        ? option.id
                                        : option;
                                const optionLabel =
                                    typeof option === "object"
                                        ? option.name || option.label
                                        : option;

                                return (
                                    <option
                                        key={`${name}-option-${index}-${optionValue}`}
                                        value={optionValue}
                                    >
                                        {optionLabel}
                                    </option>
                                );
                            })}
                    </select>

                    <div className="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                        {loading ? (
                            <Loader2 className="w-5 h-5 text-[#439454] animate-spin" />
                        ) : (
                            <ChevronDown
                                className={`w-5 h-5 transition-all duration-300 ${
                                    disabled
                                        ? "text-gray-300"
                                        : focused
                                        ? "text-[#439454] scale-110"
                                        : "text-gray-400"
                                }`}
                            />
                        )}
                    </div>
                </div>

                {(error || apiError) && (
                    <div className="flex items-start gap-2 p-3 text-sm text-red-700 border border-red-200 rounded-lg bg-red-50">
                        <AlertCircle className="w-4 h-4 mt-0.5 flex-shrink-0" />
                        <div className="flex-1">
                            <div className="font-medium">
                                {error || apiError}
                            </div>
                            {apiError && apiError.includes("UnitSeeder") && (
                                <div className="mt-1 text-xs text-red-600">
                                    Solusi: Jalankan{" "}
                                    <code className="px-1 bg-red-100 rounded">
                                        php artisan db:seed --class=UnitSeeder
                                    </code>
                                </div>
                            )}
                        </div>
                    </div>
                )}
            </div>
        );
    };

    return (
        <div className="space-y-6">
            <div className="p-6 bg-gradient-to-r from-[#439454]/5 to-[#367a41]/5 rounded-xl border border-[#439454]/20">
                <h3 className="flex items-center gap-2 mb-6 text-lg font-bold text-gray-900">
                    <Building2 className="w-5 h-5 text-[#439454]" />
                    Struktur Organisasi
                </h3>

                <div className="grid grid-cols-1 gap-6">
                    {/* Unit Organisasi Dropdown */}
                    <DropdownField
                        label="Unit Organisasi"
                        name="unit_organisasi"
                        value={data.unit_organisasi}
                        options={unitOrganisasiOptions}
                        onChange={handleUnitOrganisasiChange}
                        error={errors.unit_organisasi}
                        placeholder="Pilih Unit Organisasi"
                        loading={loading.unitOrganisasi}
                        required={true}
                    />

                    {/* Unit Dropdown - hanya muncul jika unit organisasi dipilih */}
                    {data.unit_organisasi && (
                        <DropdownField
                            label="Unit"
                            name="unit_id"
                            value={data.unit_id}
                            options={unitOptions}
                            onChange={handleUnitChange}
                            error={errors.unit_id}
                            apiError={apiErrors.unit}
                            placeholder="Pilih Unit"
                            loading={loading.unit}
                            disabled={unitOptions.length === 0 && !loading.unit}
                            required={true}
                        />
                    )}

                    {/* Sub Unit Dropdown - hanya muncul jika unit dipilih */}
                    {data.unit_id && (
                        <DropdownField
                            label="Sub Unit"
                            name="sub_unit_id"
                            value={data.sub_unit_id}
                            options={subUnitOptions}
                            onChange={handleSubUnitChange}
                            error={errors.sub_unit_id}
                            apiError={apiErrors.subUnit}
                            placeholder="Pilih Sub Unit"
                            loading={loading.subUnit}
                            disabled={
                                subUnitOptions.length === 0 && !loading.subUnit
                            }
                            required={true}
                        />
                    )}
                </div>

                {/* Preview organisasi structure */}
                {(data.unit_organisasi || data.unit_id || data.sub_unit_id) && (
                    <div className="p-4 mt-6 bg-white border border-gray-200 rounded-lg shadow-sm">
                        <h4 className="flex items-center gap-2 mb-3 text-sm font-semibold text-gray-700">
                            <CheckCircle className="w-4 h-4 text-green-500" />
                            Preview Struktur Organisasi:
                        </h4>
                        <div className="flex flex-wrap items-center gap-2 text-sm text-gray-800">
                            {data.unit_organisasi && (
                                <span className="px-3 py-1 bg-[#439454] text-white rounded-full font-medium">
                                    {data.unit_organisasi}
                                </span>
                            )}
                            {data.unit_id && unitOptions.length > 0 && (
                                <>
                                    <span className="text-gray-400">→</span>
                                    <span className="px-3 py-1 font-medium text-blue-800 bg-blue-100 rounded-full">
                                        {unitOptions.find(
                                            (u) => u.id == data.unit_id
                                        )?.name || `Unit ID: ${data.unit_id}`}
                                    </span>
                                </>
                            )}
                            {data.sub_unit_id && subUnitOptions.length > 0 && (
                                <>
                                    <span className="text-gray-400">→</span>
                                    <span className="px-3 py-1 font-medium text-green-800 bg-green-100 rounded-full">
                                        {subUnitOptions.find(
                                            (su) => su.id == data.sub_unit_id
                                        )?.name ||
                                            `Sub Unit ID: ${data.sub_unit_id}`}
                                    </span>
                                </>
                            )}
                        </div>
                    </div>
                )}
            </div>
        </div>
    );
};

export default UnitOrganisasiComponent;
