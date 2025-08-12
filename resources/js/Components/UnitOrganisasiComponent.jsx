import React, { useState, useEffect } from "react";
import { Building2, ChevronDown, Loader2 } from "lucide-react";

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
        console.log(
            "UnitOrganisasi Component initialized with options:",
            staticUnitOrganisasi
        );
    }, []);

    // Load units ketika unit organisasi dipilih
    useEffect(() => {
        if (data.unit_organisasi) {
            console.log("Unit organisasi changed to:", data.unit_organisasi);
            loadUnits(data.unit_organisasi);
        } else {
            console.log(
                "Unit organisasi cleared, resetting dependent dropdowns"
            );
            setUnitOptions([]);
            setSubUnitOptions([]);
            if (typeof setData === "function") {
                setData("unit_id", "");
                setData("sub_unit_id", "");
            }
        }
    }, [data.unit_organisasi]);

    // Load sub units ketika unit dipilih
    useEffect(() => {
        if (data.unit_id) {
            console.log("Unit changed to:", data.unit_id);
            loadSubUnits(data.unit_id);
        } else {
            console.log("Unit cleared, resetting sub units");
            setSubUnitOptions([]);
            if (typeof setData === "function") {
                setData("sub_unit_id", "");
            }
        }
    }, [data.unit_id]);

    const loadUnits = async (unitOrganisasi) => {
        setLoading((prev) => ({ ...prev, unit: true }));
        console.log("Loading units for unit organisasi:", unitOrganisasi);

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

            console.log("Units API response status:", response.status);

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const result = await response.json();
            console.log("Units API response data:", result);

            if (result.success && Array.isArray(result.data)) {
                setUnitOptions(result.data);
                console.log(
                    `Units loaded successfully: ${result.data.length} units found`
                );
            } else {
                setUnitOptions([]);
                console.warn(
                    "Failed to load units or invalid response format:",
                    result.message || "Unknown error"
                );
            }
        } catch (error) {
            console.error("Error loading units:", error);
            setUnitOptions([]);
        } finally {
            setLoading((prev) => ({ ...prev, unit: false }));
        }
    };

    const loadSubUnits = async (unitId) => {
        setLoading((prev) => ({ ...prev, subUnit: true }));
        console.log("Loading sub units for unit ID:", unitId);

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

            console.log("Sub units API response status:", response.status);

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const result = await response.json();
            console.log("Sub units API response data:", result);

            if (result.success && Array.isArray(result.data)) {
                setSubUnitOptions(result.data);
                console.log(
                    `Sub units loaded successfully: ${result.data.length} sub units found`
                );
            } else {
                setSubUnitOptions([]);
                console.log(
                    "No sub units found or failed to load:",
                    result.message || "No data available"
                );
            }
        } catch (error) {
            console.error("Error loading sub units:", error);
            setSubUnitOptions([]);
        } finally {
            setLoading((prev) => ({ ...prev, subUnit: false }));
        }
    };

    const handleUnitOrganisasiChange = (value) => {
        console.log("Unit organisasi selection changed to:", value);

        if (typeof setData === "function") {
            setData("unit_organisasi", value);
            setData("unit_id", "");
            setData("sub_unit_id", "");
        } else {
            console.error("setData is not a function:", typeof setData);
        }

        // Reset dependent fields and options
        setUnitOptions([]);
        setSubUnitOptions([]);
    };

    const handleUnitChange = (value) => {
        console.log("Unit selection changed to:", value);

        if (typeof setData === "function") {
            setData("unit_id", value);
            setData("sub_unit_id", "");
        } else {
            console.error("setData is not a function:", typeof setData);
        }

        // Reset dependent field and options
        setSubUnitOptions([]);
    };

    const handleSubUnitChange = (value) => {
        console.log("Sub unit selection changed to:", value);

        if (typeof setData === "function") {
            setData("sub_unit_id", value);
        } else {
            console.error("setData is not a function:", typeof setData);
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
        placeholder,
        disabled = false,
    }) => {
        const [focused, setFocused] = useState(false);

        // Debug logging for dropdown
        useEffect(() => {
            console.log(
                `Dropdown ${name} - Options count: ${
                    options?.length || 0
                }, Value: ${value}, Disabled: ${disabled}, Loading: ${loading}`
            );
        }, [options, value, disabled, loading, name]);

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
                            console.log(
                                `Dropdown ${name} changed to:`,
                                selectedValue
                            );
                            onChange(selectedValue);
                        }}
                        onFocus={() => {
                            console.log(`Dropdown ${name} focused`);
                            setFocused(true);
                        }}
                        onBlur={() => {
                            console.log(`Dropdown ${name} blurred`);
                            setFocused(false);
                        }}
                        disabled={disabled || loading}
                        className={`w-full px-4 py-3 pr-10 text-gray-900 transition-all duration-300 border-2 rounded-xl focus:ring-4 focus:ring-[#439454]/20 focus:border-[#439454] hover:border-[#439454]/60 disabled:opacity-50 disabled:cursor-not-allowed appearance-none ${
                            error
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
                                        ? option.name
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
                            <ChevronDown className="w-5 h-5 text-gray-400" />
                        )}
                    </div>
                </div>

                {error && <p className="text-sm text-red-600">{error}</p>}

                {/* Debug info - remove in production */}
                {process.env.NODE_ENV === "development" && (
                    <div className="text-xs text-gray-400">
                        Debug: {options?.length || 0} options, Value:{" "}
                        {value || "empty"}, Loading: {loading.toString()}
                    </div>
                )}
            </div>
        );
    };

    return (
        <div className="space-y-6">
            <div className="p-6 bg-gradient-to-r from-[#439454]/5 to-[#367a41]/5 rounded-xl border border-[#439454]/20">
                <h3 className="flex items-center gap-2 mb-4 text-lg font-bold text-gray-900">
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
                            placeholder="Pilih Unit"
                            loading={loading.unit}
                            disabled={unitOptions.length === 0 && !loading.unit}
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
                            placeholder="Pilih Sub Unit"
                            loading={loading.subUnit}
                            disabled={
                                subUnitOptions.length === 0 && !loading.subUnit
                            }
                        />
                    )}
                </div>

                {/* Preview organisasi structure */}
                {(data.unit_organisasi || data.unit_id || data.sub_unit_id) && (
                    <div className="p-4 mt-4 bg-white border border-gray-200 rounded-lg">
                        <h4 className="mb-2 text-sm font-semibold text-gray-600">
                            Preview Struktur:
                        </h4>
                        <div className="flex items-center gap-2 text-sm text-gray-800">
                            {data.unit_organisasi && (
                                <span className="px-2 py-1 bg-[#439454] text-white rounded">
                                    {data.unit_organisasi}
                                </span>
                            )}
                            {data.unit_id && unitOptions.length > 0 && (
                                <>
                                    <span className="text-gray-400">→</span>
                                    <span className="px-2 py-1 text-blue-800 bg-blue-100 rounded">
                                        {unitOptions.find(
                                            (u) => u.id == data.unit_id
                                        )?.name || `Unit ID: ${data.unit_id}`}
                                    </span>
                                </>
                            )}
                            {data.sub_unit_id && subUnitOptions.length > 0 && (
                                <>
                                    <span className="text-gray-400">→</span>
                                    <span className="px-2 py-1 text-green-800 bg-green-100 rounded">
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

                {/* Debug Panel - hanya muncul dalam development */}
                {process.env.NODE_ENV === "development" && (
                    <div className="p-4 mt-4 bg-gray-100 border border-gray-200 rounded-lg">
                        <h4 className="mb-2 text-sm font-semibold text-gray-600">
                            Debug Info:
                        </h4>
                        <div className="space-y-1 text-xs text-gray-700">
                            <div>
                                Unit Organisasi:{" "}
                                {data.unit_organisasi || "Not selected"}
                            </div>
                            <div>Unit ID: {data.unit_id || "Not selected"}</div>
                            <div>
                                Sub Unit ID:{" "}
                                {data.sub_unit_id || "Not selected"}
                            </div>
                            <div>
                                Unit Options: {unitOptions.length} available
                            </div>
                            <div>
                                Sub Unit Options: {subUnitOptions.length}{" "}
                                available
                            </div>
                            <div>
                                Loading States: Unit={loading.unit.toString()},
                                SubUnit={loading.subUnit.toString()}
                            </div>
                            <div>setData Type: {typeof setData}</div>
                        </div>
                    </div>
                )}
            </div>
        </div>
    );
};

export default UnitOrganisasiComponent;
