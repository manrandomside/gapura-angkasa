import React, { useState, useEffect } from "react";
import {
    Building2,
    ChevronDown,
    Loader2,
    AlertCircle,
    CheckCircle,
    Info,
} from "lucide-react";

const UnitOrganisasiComponent = ({
    data,
    setData,
    errors = {},
    required = false,
    clearErrors = null, // Add clearErrors function support
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
    const [isSubUnitRequired, setIsSubUnitRequired] = useState(true);

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

    // Unit organisasi yang tidak memiliki sub unit
    const unitWithoutSubUnits = ["EGM", "GM"];

    // Initialize unit organisasi options
    useEffect(() => {
        setUnitOrganisasiOptions(staticUnitOrganisasi);
    }, []);

    // Check if sub unit is required based on unit organisasi
    useEffect(() => {
        if (data.unit_organisasi) {
            const isRequired = !unitWithoutSubUnits.includes(
                data.unit_organisasi
            );
            setIsSubUnitRequired(isRequired);

            // Jika unit organisasi tidak memerlukan sub unit, kosongkan sub_unit_id dan clear error
            if (!isRequired) {
                if (typeof setData === "function") {
                    setData("sub_unit_id", "");
                }
                // Clear sub_unit_id error dari parent component
                if (typeof clearErrors === "function") {
                    clearErrors("sub_unit_id");
                }
                // Clear local API error
                setApiErrors((prev) => ({ ...prev, subUnit: null }));
            }
        }
    }, [data.unit_organisasi, setData, clearErrors]);

    // Load units ketika unit organisasi dipilih
    useEffect(() => {
        if (data.unit_organisasi) {
            loadUnits(data.unit_organisasi);
        } else {
            resetDependentFields();
        }
    }, [data.unit_organisasi]);

    // Load sub units ketika unit dipilih (hanya jika required)
    useEffect(() => {
        if (data.unit_id && isSubUnitRequired) {
            loadSubUnits(data.unit_id);
        } else {
            setSubUnitOptions([]);
            setApiErrors((prev) => ({ ...prev, subUnit: null }));
            // Hanya reset sub_unit_id jika tidak required atau unit_id kosong
            if (!isSubUnitRequired || !data.unit_id) {
                if (typeof setData === "function") {
                    setData("sub_unit_id", "");
                }
                // Clear error jika sub unit tidak required
                if (!isSubUnitRequired && typeof clearErrors === "function") {
                    clearErrors("sub_unit_id");
                }
            }
        }
    }, [data.unit_id, isSubUnitRequired, setData, clearErrors]);

    const resetDependentFields = () => {
        setUnitOptions([]);
        setSubUnitOptions([]);
        setApiErrors({ unit: null, subUnit: null });
        setIsSubUnitRequired(true);
        if (typeof setData === "function") {
            setData("unit_id", "");
            setData("sub_unit_id", "");
        }
        // Clear errors when resetting
        if (typeof clearErrors === "function") {
            clearErrors("unit_id");
            clearErrors("sub_unit_id");
        }
    };

    const loadUnits = async (unitOrganisasi) => {
        setLoading((prev) => ({ ...prev, unit: true }));
        setApiErrors((prev) => ({ ...prev, unit: null }));

        try {
            const response = await fetch(
                `/api/units/by-organisasi?unit_organisasi=${encodeURIComponent(
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

                // Auto-select jika hanya ada satu unit
                if (result.data.length === 1) {
                    if (typeof setData === "function") {
                        setData("unit_id", result.data[0].id.toString());
                    }
                }

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
                `/api/sub-units/by-unit?unit_id=${encodeURIComponent(unitId)}`,
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

            if (result.success) {
                if (Array.isArray(result.data)) {
                    setSubUnitOptions(result.data);

                    // Auto-select jika hanya ada satu sub unit
                    if (result.data.length === 1) {
                        if (typeof setData === "function") {
                            setData(
                                "sub_unit_id",
                                result.data[0].id.toString()
                            );
                        }
                    }
                } else {
                    // Handle kasus unit tanpa sub unit (EGM, GM)
                    setSubUnitOptions([]);
                    if (
                        result.meta &&
                        result.meta.unit_info &&
                        !result.meta.unit_info.requires_sub_unit
                    ) {
                        // Unit tidak memerlukan sub unit, ini normal
                        setApiErrors((prev) => ({ ...prev, subUnit: null }));
                    }
                }
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

        // Clear sub unit error when changing unit
        if (typeof clearErrors === "function") {
            clearErrors("sub_unit_id");
        }
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
        note = null,
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
                    {!required && (
                        <span className="text-xs text-gray-500">
                            (Optional)
                        </span>
                    )}
                </label>

                {note && (
                    <div className="flex items-center gap-2 p-3 text-sm text-blue-700 border border-blue-200 rounded-lg bg-blue-50">
                        <Info className="flex-shrink-0 w-4 h-4" />
                        <span>{note}</span>
                    </div>
                )}

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
                    <span className="text-sm font-normal text-gray-600">
                        (Lengkapi sesuai struktur organisasi)
                    </span>
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
                            placeholder={
                                unitOptions.length === 0 && !loading.unit
                                    ? "Tidak ada unit tersedia"
                                    : "Pilih Unit"
                            }
                            loading={loading.unit}
                            disabled={unitOptions.length === 0 && !loading.unit}
                            required={true}
                        />
                    )}

                    {/* Sub Unit Dropdown - conditional berdasarkan unit organisasi */}
                    {data.unit_id && (
                        <>
                            {!isSubUnitRequired ? (
                                <div className="space-y-2">
                                    <label className="flex items-center gap-2 text-sm font-semibold text-gray-700">
                                        <Building2 className="w-4 h-4 text-gray-400" />
                                        Sub Unit
                                        <span className="text-xs text-gray-500">
                                            (Tidak diperlukan)
                                        </span>
                                    </label>
                                    <div className="flex items-center gap-2 p-4 text-sm text-blue-700 border border-blue-200 rounded-xl bg-blue-50">
                                        <Info className="flex-shrink-0 w-5 h-5" />
                                        <div>
                                            <div className="font-medium">
                                                Unit organisasi{" "}
                                                {data.unit_organisasi} tidak
                                                memiliki struktur sub unit
                                            </div>
                                            <div className="mt-1 text-xs text-blue-600">
                                                Struktur organisasi untuk{" "}
                                                {data.unit_organisasi} hanya
                                                sampai level unit
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            ) : (
                                <DropdownField
                                    label="Sub Unit"
                                    name="sub_unit_id"
                                    value={data.sub_unit_id}
                                    options={subUnitOptions}
                                    onChange={handleSubUnitChange}
                                    error={errors.sub_unit_id}
                                    apiError={apiErrors.subUnit}
                                    placeholder={
                                        subUnitOptions.length === 0 &&
                                        !loading.subUnit
                                            ? "Tidak ada sub unit tersedia"
                                            : "Pilih Sub Unit"
                                    }
                                    loading={loading.subUnit}
                                    disabled={
                                        subUnitOptions.length === 0 &&
                                        !loading.subUnit
                                    }
                                    required={true}
                                    note={
                                        data.unit_id &&
                                        subUnitOptions.length === 0 &&
                                        !loading.subUnit
                                            ? "Sub unit akan muncul setelah data dimuat dari server"
                                            : null
                                    }
                                />
                            )}
                        </>
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
                            {isSubUnitRequired ? (
                                data.sub_unit_id &&
                                subUnitOptions.length > 0 && (
                                    <>
                                        <span className="text-gray-400">→</span>
                                        <span className="px-3 py-1 font-medium text-green-800 bg-green-100 rounded-full">
                                            {subUnitOptions.find(
                                                (su) =>
                                                    su.id == data.sub_unit_id
                                            )?.name ||
                                                `Sub Unit ID: ${data.sub_unit_id}`}
                                        </span>
                                    </>
                                )
                            ) : (
                                <>
                                    <span className="text-gray-400">→</span>
                                    <span className="px-3 py-1 font-medium text-gray-600 bg-gray-100 rounded-full">
                                        Tidak ada sub unit
                                    </span>
                                </>
                            )}
                        </div>

                        {/* Status indicator */}
                        <div className="mt-3 text-xs text-gray-600">
                            {isSubUnitRequired ? (
                                data.sub_unit_id ? (
                                    <span className="flex items-center gap-1 text-green-600">
                                        <CheckCircle className="w-3 h-3" />
                                        Struktur organisasi lengkap
                                    </span>
                                ) : (
                                    <span className="flex items-center gap-1 text-orange-600">
                                        <AlertCircle className="w-3 h-3" />
                                        Sub unit masih perlu dipilih
                                    </span>
                                )
                            ) : (
                                <span className="flex items-center gap-1 text-blue-600">
                                    <Info className="w-3 h-3" />
                                    Struktur organisasi sesuai untuk{" "}
                                    {data.unit_organisasi}
                                </span>
                            )}
                        </div>
                    </div>
                )}

                {/* Information panel */}
                {data.unit_organisasi && (
                    <div className="p-3 mt-4 border border-gray-200 rounded-lg bg-gray-50">
                        <div className="text-xs text-gray-600">
                            <div className="mb-1 font-medium text-gray-700">
                                Informasi:
                            </div>
                            {unitWithoutSubUnits.includes(
                                data.unit_organisasi
                            ) ? (
                                <div>
                                    • Unit organisasi{" "}
                                    <span className="font-medium">
                                        {data.unit_organisasi}
                                    </span>{" "}
                                    tidak memiliki struktur sub unit
                                    <br />• Struktur berakhir di level unit
                                </div>
                            ) : (
                                <div>
                                    • Unit organisasi{" "}
                                    <span className="font-medium">
                                        {data.unit_organisasi}
                                    </span>{" "}
                                    memiliki struktur sub unit
                                    <br />• Sub unit wajib dipilih untuk
                                    melengkapi struktur organisasi
                                </div>
                            )}
                        </div>
                    </div>
                )}
            </div>
        </div>
    );
};

export default UnitOrganisasiComponent;
