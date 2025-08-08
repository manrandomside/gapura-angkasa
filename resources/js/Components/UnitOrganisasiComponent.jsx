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
    }, []);

    // Load units ketika unit organisasi dipilih
    useEffect(() => {
        if (data.unit_organisasi) {
            loadUnits(data.unit_organisasi);
        } else {
            setUnitOptions([]);
            setSubUnitOptions([]);
            setData("unit_id", "");
            setData("sub_unit_id", "");
        }
    }, [data.unit_organisasi]);

    // Load sub units ketika unit dipilih
    useEffect(() => {
        if (data.unit_id) {
            loadSubUnits(data.unit_id);
        } else {
            setSubUnitOptions([]);
            setData("sub_unit_id", "");
        }
    }, [data.unit_id]);

    const loadUnits = async (unitOrganisasi) => {
        setLoading((prev) => ({ ...prev, unit: true }));
        try {
            const response = await fetch(
                `/api/units?unit_organisasi=${encodeURIComponent(
                    unitOrganisasi
                )}`
            );
            const data = await response.json();

            if (data.success) {
                setUnitOptions(data.data);
            } else {
                setUnitOptions([]);
                console.error("Failed to load units:", data.message);
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
        try {
            const response = await fetch(
                `/api/sub-units?unit_id=${encodeURIComponent(unitId)}`
            );
            const data = await response.json();

            if (data.success) {
                setSubUnitOptions(data.data);
            } else {
                setSubUnitOptions([]);
                console.error("Failed to load sub units:", data.message);
            }
        } catch (error) {
            console.error("Error loading sub units:", error);
            setSubUnitOptions([]);
        } finally {
            setLoading((prev) => ({ ...prev, subUnit: false }));
        }
    };

    const handleUnitOrganisasiChange = (value) => {
        setData("unit_organisasi", value);
        // Reset dependent fields
        setData("unit_id", "");
        setData("sub_unit_id", "");
    };

    const handleUnitChange = (value) => {
        setData("unit_id", value);
        // Reset dependent field
        setData("sub_unit_id", "");
    };

    const handleSubUnitChange = (value) => {
        setData("sub_unit_id", value);
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
                        onChange={(e) => onChange(e.target.value)}
                        onFocus={() => setFocused(true)}
                        onBlur={() => setFocused(false)}
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
                        {options.map((option) => (
                            <option
                                key={
                                    typeof option === "object"
                                        ? option.id
                                        : option
                                }
                                value={
                                    typeof option === "object"
                                        ? option.id
                                        : option
                                }
                            >
                                {typeof option === "object"
                                    ? option.name
                                    : option}
                            </option>
                        ))}
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
                                        {
                                            unitOptions.find(
                                                (u) => u.id == data.unit_id
                                            )?.name
                                        }
                                    </span>
                                </>
                            )}
                            {data.sub_unit_id && subUnitOptions.length > 0 && (
                                <>
                                    <span className="text-gray-400">→</span>
                                    <span className="px-2 py-1 text-green-800 bg-green-100 rounded">
                                        {
                                            subUnitOptions.find(
                                                (su) =>
                                                    su.id == data.sub_unit_id
                                            )?.name
                                        }
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
