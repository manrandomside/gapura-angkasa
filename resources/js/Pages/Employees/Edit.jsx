import React, { useState, useEffect } from "react";
import { Head, useForm, router } from "@inertiajs/react";
import DashboardLayout from "@/Layouts/DashboardLayout";
import {
    User,
    ArrowLeft,
    Save,
    X,
    Loader2,
    Building2,
    GraduationCap,
    FileText,
    MapPin,
    Calendar,
    Phone,
    Mail,
    Shield,
    Users,
    UserCheck,
    Briefcase,
    CheckCircle,
    AlertCircle,
    Info,
    AlertTriangle,
} from "lucide-react";

// Enhanced InputField component with fixed styling
const InputField = ({
    name,
    label,
    type = "text",
    required = false,
    options = null,
    placeholder = "",
    icon: Icon = null,
    value,
    onChange,
    onBlur,
    error,
    disabled = false,
    hint = null,
    readonly = false,
}) => {
    const [focused, setFocused] = useState(false);
    const [hasValue, setHasValue] = useState(false);

    useEffect(() => {
        setHasValue(Boolean(value));
    }, [value]);

    const handleChange = (e) => {
        if (!readonly && !disabled) {
            onChange(name, e.target.value);
            setHasValue(Boolean(e.target.value));
        }
    };

    const handleBlur = (e) => {
        setFocused(false);
        if (onBlur && !readonly && !disabled) {
            onBlur(name, e.target.value);
        }
    };

    const handleFocus = () => {
        if (!readonly && !disabled) {
            setFocused(true);
        }
    };

    const getInputClasses = () => {
        let baseClasses =
            "w-full px-4 py-3 text-gray-900 transition-all duration-300 border-2 rounded-xl focus:outline-none";

        if (readonly) {
            return `${baseClasses} bg-gray-100 border-gray-300 cursor-not-allowed text-gray-600`;
        }

        if (disabled) {
            return `${baseClasses} border-gray-300 bg-gray-50 cursor-not-allowed opacity-50`;
        }

        if (error) {
            return `${baseClasses} border-red-300 bg-red-50 focus:border-red-400 focus:ring-2 focus:ring-red-100`;
        }

        if (focused) {
            return `${baseClasses} border-[#439454] bg-white shadow-md focus:border-[#439454] focus:ring-2 focus:ring-[#439454]/20`;
        }

        return `${baseClasses} border-gray-300 bg-white hover:border-[#439454]/60 focus:border-[#439454] focus:ring-2 focus:ring-[#439454]/20`;
    };

    const renderInput = () => {
        if (options) {
            // Handle both array of strings and array of objects
            const optionItems = options.map((option, index) => {
                if (typeof option === "object" && option.value !== undefined) {
                    return (
                        <option key={index} value={option.value}>
                            {option.label}
                        </option>
                    );
                } else {
                    return (
                        <option key={index} value={option}>
                            {option}
                        </option>
                    );
                }
            });

            return (
                <select
                    name={name}
                    value={value || ""}
                    onChange={handleChange}
                    onBlur={handleBlur}
                    onFocus={handleFocus}
                    disabled={disabled || readonly}
                    className={getInputClasses()}
                >
                    <option value="">{placeholder || `Pilih ${label}`}</option>
                    {optionItems}
                </select>
            );
        }

        // FIXED: For alamat field, use textarea for better UX
        if (name === "alamat") {
            return (
                <textarea
                    name={name}
                    value={value || ""}
                    onChange={handleChange}
                    onBlur={handleBlur}
                    onFocus={handleFocus}
                    placeholder={readonly ? "" : placeholder}
                    disabled={disabled}
                    readOnly={readonly}
                    rows={3}
                    className={getInputClasses()}
                />
            );
        }

        return (
            <input
                type={type}
                name={name}
                value={value || ""}
                onChange={handleChange}
                onBlur={handleBlur}
                onFocus={handleFocus}
                placeholder={readonly ? "" : placeholder}
                disabled={disabled}
                readOnly={readonly}
                className={getInputClasses()}
            />
        );
    };

    return (
        <div className="space-y-2">
            <label className="flex items-center gap-2 text-sm font-semibold text-gray-700">
                {Icon && (
                    <Icon
                        className={`w-4 h-4 transition-colors duration-200 ${
                            focused || hasValue
                                ? "text-[#439454]"
                                : "text-gray-400"
                        }`}
                    />
                )}
                {label}
                {required && <span className="text-red-500">*</span>}
                {readonly && (
                    <span className="ml-1 text-xs text-gray-500">
                        (Tidak dapat diubah)
                    </span>
                )}
            </label>

            {renderInput()}

            {error && (
                <div className="flex items-center gap-2 text-sm text-red-600">
                    <AlertCircle className="w-4 h-4" />
                    {error}
                </div>
            )}

            {hint && !error && (
                <div className="flex items-center gap-2 text-sm text-gray-500">
                    <Info className="w-4 h-4" />
                    {hint}
                </div>
            )}
        </div>
    );
};

// Enhanced Form Notification Component
const FormNotification = ({ type, title, message, onClose }) => {
    const [visible, setVisible] = useState(true);

    useEffect(() => {
        // Auto-dismiss only for success notifications
        if (type === "success") {
            const timer = setTimeout(() => {
                setVisible(false);
                setTimeout(onClose, 300);
            }, 3000);

            return () => clearTimeout(timer);
        }
    }, [onClose, type]);

    const getIcon = () => {
        switch (type) {
            case "success":
                return <CheckCircle className="w-5 h-5 text-green-500" />;
            case "error":
                return <AlertCircle className="w-5 h-5 text-red-500" />;
            case "warning":
                return <AlertTriangle className="w-5 h-5 text-yellow-500" />;
            case "info":
                return <Info className="w-5 h-5 text-blue-500" />;
            default:
                return <Info className="w-5 h-5 text-blue-500" />;
        }
    };

    const getBgColor = () => {
        switch (type) {
            case "success":
                return "bg-green-50 border-green-200";
            case "error":
                return "bg-red-50 border-red-200";
            case "warning":
                return "bg-yellow-50 border-yellow-200";
            case "info":
                return "bg-blue-50 border-blue-200";
            default:
                return "bg-blue-50 border-blue-200";
        }
    };

    return (
        <div
            className={`fixed top-4 right-4 z-50 p-4 border rounded-lg shadow-lg transition-all duration-300 transform ${getBgColor()} ${
                visible
                    ? "opacity-100 translate-x-0"
                    : "opacity-0 translate-x-full"
            }`}
        >
            <div className="flex items-start gap-3">
                {getIcon()}
                <div className="flex-1">
                    <h4 className="font-semibold text-gray-900">{title}</h4>
                    <p className="text-sm text-gray-600">{message}</p>
                </div>
                {onClose && (
                    <button
                        onClick={() => {
                            setVisible(false);
                            setTimeout(onClose, 300);
                        }}
                        className="text-gray-400 hover:text-gray-600"
                    >
                        <X className="w-4 h-4" />
                    </button>
                )}
            </div>
        </div>
    );
};

// Helper function to properly convert jenis_kelamin
const convertJenisKelaminToDisplay = (jenisKelamin) => {
    if (!jenisKelamin) return "";

    // Handle both database formats
    if (jenisKelamin === "L" || jenisKelamin === "Laki-laki") {
        return "Laki-laki";
    } else if (jenisKelamin === "P" || jenisKelamin === "Perempuan") {
        return "Perempuan";
    }

    return jenisKelamin; // fallback
};

// UPDATED: Function untuk format unit display dengan kode
const formatUnitDisplay = (unit, unitOrganisasi) => {
    // Mapping kode unit berdasarkan unit organisasi
    const unitCodeMapping = {
        Airside: {
            "Movement Operations": "MO",
            "Maintenance Equipment": "ME",
        },
        Landside: {
            "Movement Flight": "MF",
            "Movement Service": "MS",
        },
        "Back Office": {
            "Management Keuangan": "MK",
            "Management Unit": "MU",
        },
        SSQC: {
            "Management Quality": "MQ",
        },
        Ancillary: {
            "Management Business": "MB",
        },
    };

    const unitName = unit.name || unit.label || unit;

    // Cari kode unit berdasarkan unit organisasi dan nama unit
    if (unitOrganisasi && unitCodeMapping[unitOrganisasi]) {
        const code = unitCodeMapping[unitOrganisasi][unitName];
        if (code) {
            return `(${code}) ${unitName}`;
        }
    }

    // Fallback ke nama unit saja jika tidak ditemukan mapping
    return unitName;
};

// UPDATED: Function untuk mendapatkan kode unit dari display name
const getUnitCodeFromDisplay = (displayName) => {
    if (!displayName) return null;

    const match = displayName.match(/^\(([A-Z]+)\)/);
    return match ? match[1] : null;
};

// FIXED: Helper function to calculate masa kerja with proper range (simplified)
const calculateMasaKerja = (tmtMulaiKerja, tmtBerakhirKerja = null) => {
    if (!tmtMulaiKerja) return "";

    const startDate = new Date(tmtMulaiKerja);
    const endDate = tmtBerakhirKerja ? new Date(tmtBerakhirKerja) : new Date();

    // Validate dates
    if (isNaN(startDate.getTime()) || isNaN(endDate.getTime())) {
        return "Tanggal tidak valid";
    }

    // If end date is before start date, show error
    if (endDate < startDate) {
        return "Tanggal berakhir sebelum tanggal mulai";
    }

    let years = endDate.getFullYear() - startDate.getFullYear();
    let months = endDate.getMonth() - startDate.getMonth();

    // Adjust for negative months
    if (months < 0) {
        years--;
        months += 12;
    }

    // Simple display: only years and months, no day calculation
    if (years > 0 && months > 0) {
        return `${years} tahun ${months} bulan`;
    } else if (years > 0) {
        return `${years} tahun`;
    } else if (months > 0) {
        return `${months} bulan`;
    } else {
        return "Kurang dari 1 bulan";
    }
};

export default function Edit({
    employee,
    organizations = [],
    unitOrganisasiOptions = [],
    unitOptions = [],
    subUnitOptions = [],
    jabatanOptions = [],
    kelompokJabatanOptions = [],
    statusPegawaiOptions = [],
    providerOptions = [],
    statusKerjaOptions = [],
    success = null,
    error = null,
    message = null,
}) {
    // Initialize form data with all NEW FIELDS
    const initializeFormData = () => {
        console.log("Raw employee data:", employee);
        console.log("Employee jenis_kelamin:", employee?.jenis_kelamin);

        const convertedJenisKelamin = convertJenisKelaminToDisplay(
            employee?.jenis_kelamin
        );
        console.log("Converted jenis_kelamin:", convertedJenisKelamin);

        return {
            // Data Pribadi
            nik: employee?.nik || "",
            nip: employee?.nip || "",
            nama_lengkap: employee?.nama_lengkap || "",
            jenis_kelamin: convertedJenisKelamin,
            tempat_lahir: employee?.tempat_lahir || "",
            tanggal_lahir: employee?.tanggal_lahir || "",
            kota_domisili: employee?.kota_domisili || "",
            alamat: employee?.alamat || "", // FIXED: Changed from alamat_lengkap to alamat
            handphone: employee?.handphone || "",
            email: employee?.email || "",
            no_bpjs_kesehatan: employee?.no_bpjs_kesehatan || "",
            no_bpjs_ketenagakerjaan: employee?.no_bpjs_ketenagakerjaan || "",

            // Data Pekerjaan & Struktur Organisasi
            unit_organisasi: employee?.unit_organisasi || "",
            unit_id: employee?.unit_id?.toString() || "",
            sub_unit_id: employee?.sub_unit_id?.toString() || "",
            nama_jabatan: employee?.nama_jabatan || "",
            jabatan: employee?.jabatan || "",
            kelompok_jabatan: employee?.kelompok_jabatan || "",
            status_pegawai: employee?.status_pegawai || "",
            tmt_mulai_kerja: employee?.tmt_mulai_kerja || "",
            tmt_mulai_jabatan: employee?.tmt_mulai_jabatan || "",
            tmt_pensiun: employee?.tmt_pensiun || "",

            // NEW FIELDS
            status_kerja: employee?.status_kerja || "Non-Aktif",
            tmt_berakhir_kerja: employee?.tmt_berakhir_kerja || "",
            tmt_akhir_jabatan: employee?.tmt_akhir_jabatan || "",
            provider: employee?.provider || "",
            unit_kerja_kontrak: employee?.unit_kerja_kontrak || "",
            grade: employee?.grade || "",
            lokasi_kerja: employee?.lokasi_kerja || "Bandar Udara Ngurah Rai",
            cabang: employee?.cabang || "DPS",
            masa_kerja:
                employee?.masa_kerja ||
                calculateMasaKerja(
                    employee?.tmt_mulai_kerja,
                    employee?.tmt_berakhir_kerja
                ),

            // Data Pendidikan
            pendidikan_terakhir: employee?.pendidikan_terakhir || "",
            instansi_pendidikan: employee?.instansi_pendidikan || "",
            jurusan: employee?.jurusan || "",
            tahun_lulus: employee?.tahun_lulus || "",

            // Data Tambahan
            seragam: employee?.seragam || "",
            jenis_sepatu: employee?.jenis_sepatu || "",
            ukuran_sepatu: employee?.ukuran_sepatu || "",
            height: employee?.height || "",
            weight: employee?.weight || "",
        };
    };

    const { data, setData, put, processing, errors, clearErrors } = useForm(
        initializeFormData()
    );

    const [activeSection, setActiveSection] = useState("personal");
    const [formValidation, setFormValidation] = useState({});
    const [notification, setNotification] = useState(null);
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [calculatedAge, setCalculatedAge] = useState(null);

    // Available options for cascading dropdowns
    const [availableUnits, setAvailableUnits] = useState([]);
    const [availableSubUnits, setAvailableSubUnits] = useState([]);
    const [loadingUnits, setLoadingUnits] = useState(false);
    const [loadingSubUnits, setLoadingSubUnits] = useState(false);

    // Default options
    const unitOrganisasiOptionsStatic = [
        "EGM",
        "GM",
        "Airside",
        "Landside",
        "Back Office",
        "SSQC",
        "Ancillary",
    ];

    const providerOptionsDefault = [
        "PT Gapura Angkasa",
        "PT Air Box Personalia",
        "PT Finfleet Teknologi Indonesia",
        "PT Mitra Angkasa Perdana",
        "PT Safari Dharma Sakti",
        "PT Grha Humanindo Management",
        "PT Duta Griya Sarana",
        "PT Aerotrans Wisata",
        "PT Mandala Garda Nusantara",
        "PT Kidora Mandiri Investama",
    ];

    const statusKerjaOptionsDefault = [
        "Aktif",
        "Non-Aktif",
        "Pensiun",
        "Mutasi",
    ];

    // Units without sub units
    const unitWithoutSubUnits = ["EGM", "GM"];

    // Show notification dari session
    useEffect(() => {
        if (success) {
            setNotification({
                type: "success",
                title: "Berhasil!",
                message: success,
            });
        } else if (error) {
            setNotification({
                type: "error",
                title: "Terjadi Kesalahan!",
                message: error,
            });
        } else if (message) {
            setNotification({
                type: "info",
                title: "Informasi",
                message: message,
            });
        }
    }, [success, error, message]);

    // Fetch units function
    const fetchUnits = async (unitOrganisasi, showNotification = true) => {
        if (!unitOrganisasi) {
            setAvailableUnits([]);
            setAvailableSubUnits([]);
            setData("unit_id", "");
            setData("sub_unit_id", "");
            return;
        }

        setLoadingUnits(true);
        console.log("Fetching units for:", unitOrganisasi);

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
                    },
                }
            );

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const result = await response.json();
            console.log("Units API response:", result);

            if (result.success && Array.isArray(result.data)) {
                setAvailableUnits(result.data);
                console.log("Units loaded successfully:", result.data);

                if (showNotification && result.data.length > 0) {
                    setNotification({
                        type: "success",
                        title: "Berhasil!",
                        message: `${result.data.length} unit berhasil dimuat untuk ${unitOrganisasi}`,
                    });
                }
            } else {
                console.warn("No units found for:", unitOrganisasi, result);
                setAvailableUnits([]);

                if (showNotification) {
                    setNotification({
                        type: "info",
                        title: "Informasi",
                        message:
                            result.message ||
                            `Tidak ada unit tersedia untuk ${unitOrganisasi}`,
                    });
                }
            }
        } catch (error) {
            console.error("Error fetching units:", error);
            setAvailableUnits([]);

            if (showNotification) {
                setNotification({
                    type: "error",
                    title: "Kesalahan!",
                    message: `Gagal memuat unit untuk ${unitOrganisasi}: ${error.message}`,
                });
            }
        } finally {
            setLoadingUnits(false);
        }
    };

    // Fetch sub units function
    const fetchSubUnits = async (unitId, showNotification = true) => {
        if (!unitId) {
            setAvailableSubUnits([]);
            setData("sub_unit_id", "");
            return;
        }

        setLoadingSubUnits(true);
        console.log("Fetching sub units for unit_id:", unitId);

        try {
            const response = await fetch(
                `/api/sub-units?unit_id=${encodeURIComponent(unitId)}`,
                {
                    method: "GET",
                    headers: {
                        "Content-Type": "application/json",
                        Accept: "application/json",
                    },
                }
            );

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const result = await response.json();
            console.log("Sub units API response:", result);

            if (result.success && Array.isArray(result.data)) {
                setAvailableSubUnits(result.data);
                console.log("Sub units loaded successfully:", result.data);

                if (showNotification && result.data.length > 0) {
                    setNotification({
                        type: "success",
                        title: "Berhasil!",
                        message: `${result.data.length} sub unit berhasil dimuat`,
                    });
                }
            } else {
                console.warn("No sub units found for unit_id:", unitId, result);
                setAvailableSubUnits([]);

                if (showNotification && result.success === false) {
                    setNotification({
                        type: "info",
                        title: "Informasi",
                        message:
                            result.message ||
                            "Unit ini tidak memiliki sub unit",
                    });
                }
            }
        } catch (error) {
            console.error("Error fetching sub units:", error);
            setAvailableSubUnits([]);

            if (showNotification) {
                setNotification({
                    type: "error",
                    title: "Kesalahan!",
                    message: `Gagal memuat sub unit: ${error.message}`,
                });
            }
        } finally {
            setLoadingSubUnits(false);
        }
    };

    // Load initial data when component mounts
    useEffect(() => {
        console.log("Employee data on mount:", employee);
        console.log("Form data on mount:", data);

        if (employee) {
            if (employee.unit_organisasi) {
                console.log("Loading units for:", employee.unit_organisasi);
                fetchUnits(employee.unit_organisasi, false);
            }

            if (employee.unit_id) {
                console.log("Loading sub units for unit_id:", employee.unit_id);
                fetchSubUnits(employee.unit_id, false);
            }
        }
    }, []);

    // Auto-calculate TMT Pensiun when birth date changes
    useEffect(() => {
        if (data.tanggal_lahir) {
            const birthDate = new Date(data.tanggal_lahir);
            const today = new Date();
            let age = today.getFullYear() - birthDate.getFullYear();
            const monthDiff = today.getMonth() - birthDate.getMonth();

            if (
                monthDiff < 0 ||
                (monthDiff === 0 && today.getDate() < birthDate.getDate())
            ) {
                age--;
            }

            // Calculate TMT Pensiun (56 years from birth date)
            const pensionDate = new Date(birthDate);
            pensionDate.setFullYear(pensionDate.getFullYear() + 56);

            // Apply logic: if birth date < 10th, pension on 1st of same month, else 1st of next month
            if (birthDate.getDate() < 10) {
                pensionDate.setDate(1);
            } else {
                pensionDate.setDate(1);
                pensionDate.setMonth(pensionDate.getMonth() + 1);
            }

            const formatDate = (date) => {
                return date.toISOString().split("T")[0];
            };

            setData("tmt_pensiun", formatDate(pensionDate));
            setCalculatedAge(age);
        } else {
            setData("tmt_pensiun", "");
            setCalculatedAge(null);
        }
    }, [data.tanggal_lahir]);

    // Auto-sync jabatan fields
    useEffect(() => {
        if (data.nama_jabatan && !data.jabatan) {
            setData("jabatan", data.nama_jabatan);
        }
    }, [data.nama_jabatan]);

    // FIXED: AUTO-UPDATE Masa kerja when tmt_mulai_kerja or tmt_berakhir_kerja changes
    useEffect(() => {
        if (data.tmt_mulai_kerja) {
            const calculatedMasaKerja = calculateMasaKerja(
                data.tmt_mulai_kerja,
                data.tmt_berakhir_kerja || null
            );
            setData("masa_kerja", calculatedMasaKerja);
        } else {
            setData("masa_kerja", "");
        }
    }, [data.tmt_mulai_kerja, data.tmt_berakhir_kerja]);

    // Define sections
    const sections = {
        personal: { name: "Data Pribadi", icon: User },
        work: { name: "Data Pekerjaan", icon: Building2 },
        education: { name: "Pendidikan", icon: GraduationCap },
        additional: { name: "Data Tambahan", icon: FileText },
    };

    const validateField = (fieldName, value) => {
        let error = "";

        switch (fieldName) {
            case "nik":
                // UPDATED: NIK validation - Hapus validasi required, hanya validasi format jika ada value
                if (value && !/^[0-9]+$/.test(value)) {
                    error = "NIK hanya boleh berisi angka";
                } else if (value && value.length !== 16) {
                    error = "NIK harus tepat 16 digit";
                }
                // REMOVED: NIK required validation
                break;
            case "nip":
                if (value && !/^[0-9]+$/.test(value)) {
                    error = "NIP hanya boleh berisi angka";
                } else if (value && value.length < 5) {
                    error = "NIP minimal 5 digit";
                } else if (!value || value.trim() === "") {
                    error = "NIP wajib diisi";
                }
                break;
            case "email":
                if (value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                    error = "Format email tidak valid";
                }
                break;
            case "handphone":
                if (value && !/^[0-9+\-\s()]+$/.test(value)) {
                    error =
                        "Nomor handphone hanya boleh berisi angka, +, -, spasi, dan kurung";
                } else if (value && value.length < 10) {
                    error = "Nomor handphone minimal 10 digit";
                }
                break;
            case "ukuran_sepatu":
                if (value && (isNaN(value) || value < 30 || value > 50)) {
                    error = "Ukuran sepatu harus antara 30-50";
                }
                break;
            case "height":
                if (value && (isNaN(value) || value < 100 || value > 250)) {
                    error = "Tinggi badan harus antara 100-250 cm";
                }
                break;
            case "weight":
                if (value && (isNaN(value) || value < 30 || value > 200)) {
                    error = "Berat badan harus antara 30-200 kg";
                }
                break;
            case "tahun_lulus":
                if (
                    value &&
                    (isNaN(value) ||
                        value < 1950 ||
                        value > new Date().getFullYear())
                ) {
                    error = `Tahun lulus harus antara 1950-${new Date().getFullYear()}`;
                }
                break;
            case "tanggal_lahir":
                if (value) {
                    const birthDate = new Date(value);
                    const today = new Date();
                    const age = today.getFullYear() - birthDate.getFullYear();
                    if (age < 17 || age > 70) {
                        error = "Usia harus antara 17-70 tahun";
                    }
                }
                break;
            case "unit_organisasi":
                if (!value) {
                    error = "Unit organisasi wajib dipilih";
                }
                break;
            case "unit_id":
                if (!value) {
                    error = "Unit wajib dipilih";
                }
                break;
            case "sub_unit_id":
                if (
                    !unitWithoutSubUnits.includes(data.unit_organisasi) &&
                    !value
                ) {
                    error = "Sub unit wajib dipilih untuk unit organisasi ini";
                }
                break;
            // NEW VALIDATIONS
            case "tmt_berakhir_kerja":
                if (value && data.tmt_mulai_kerja) {
                    const startDate = new Date(data.tmt_mulai_kerja);
                    const endDate = new Date(value);
                    if (endDate <= startDate) {
                        error =
                            "TMT Berakhir Kerja harus setelah TMT Mulai Kerja";
                    }
                }
                break;
            case "tmt_akhir_jabatan":
                if (value && data.tmt_mulai_jabatan) {
                    const startDate = new Date(data.tmt_mulai_jabatan);
                    const endDate = new Date(value);
                    if (endDate <= startDate) {
                        error =
                            "TMT Akhir Jabatan harus setelah TMT Mulai Jabatan";
                    }
                }
                break;
        }

        setFormValidation((prev) => ({
            ...prev,
            [fieldName]: error,
        }));

        return error;
    };

    // Handle input changes with NEW FIELD logic
    const handleInputChange = (name, value) => {
        setData(name, value);

        // Handle cascading dropdown untuk struktur organisasi
        if (name === "unit_organisasi") {
            console.log("Unit Organisasi changed to:", value);

            // Reset dependent fields
            setData("unit_id", "");
            setData("sub_unit_id", "");
            setAvailableUnits([]);
            setAvailableSubUnits([]);

            // Clear sub_unit_id error jika unit organisasi tidak memerlukan sub unit
            if (unitWithoutSubUnits.includes(value)) {
                clearErrors("sub_unit_id");
                setFormValidation((prev) => ({
                    ...prev,
                    sub_unit_id: "",
                }));
            }

            // Load units untuk unit organisasi yang dipilih
            if (value) {
                fetchUnits(value, true);
            }
        } else if (name === "unit_id") {
            console.log("Unit ID changed to:", value);

            // Reset sub unit
            setData("sub_unit_id", "");
            setAvailableSubUnits([]);

            // Load sub units untuk unit yang dipilih
            if (value && !unitWithoutSubUnits.includes(data.unit_organisasi)) {
                fetchSubUnits(value, true);
            }
        }

        // Clear errors saat user mulai mengetik
        if (errors[name]) {
            clearErrors(name);
        }

        // Clear local validation error
        if (formValidation[name]) {
            setFormValidation((prev) => ({
                ...prev,
                [name]: "",
            }));
        }

        // Real-time validation for important fields
        if (
            [
                "nik",
                "nip",
                "email",
                "handphone",
                "tmt_berakhir_kerja",
                "tmt_akhir_jabatan",
            ].includes(name)
        ) {
            setTimeout(() => validateField(name, value), 500);
        }
    };

    // Handle input blur for validation
    const handleInputBlur = (name, value) => {
        validateField(name, value);
    };

    // UPDATED: Handle form submission - NIK removed from required fields
    const handleSubmit = (e) => {
        e.preventDefault();

        if (processing || isSubmitting) return;

        setIsSubmitting(true);

        // UPDATED: Validate required fields - NIK REMOVED
        const requiredFields = {
            // REMOVED: nik: "NIK wajib diisi" - NIK is now optional
            nip: "NIP wajib diisi",
            nama_lengkap: "Nama lengkap wajib diisi",
            jenis_kelamin: "Jenis kelamin wajib dipilih",
            unit_organisasi: "Unit organisasi wajib dipilih",
            unit_id: "Unit wajib dipilih",
            nama_jabatan: "Nama jabatan wajib diisi",
            kelompok_jabatan: "Kelompok jabatan wajib dipilih",
            status_pegawai: "Status pegawai wajib dipilih",
        };

        // Add sub_unit_id to required fields if unit_organisasi requires it
        if (
            data.unit_organisasi &&
            !unitWithoutSubUnits.includes(data.unit_organisasi)
        ) {
            requiredFields.sub_unit_id = "Sub unit wajib dipilih";
        }

        const missingFields = Object.keys(requiredFields).filter(
            (field) => !data[field] || data[field].toString().trim() === ""
        );

        if (missingFields.length > 0) {
            const message =
                missingFields.length === 1
                    ? requiredFields[missingFields[0]]
                    : "Mohon lengkapi semua field yang wajib diisi";

            setNotification({
                type: "error",
                title: "Data Tidak Lengkap",
                message: message,
            });
            setIsSubmitting(false);
            return;
        }

        // Check for validation errors
        const hasValidationErrors = Object.values(formValidation).some(
            (error) => error !== ""
        );
        if (hasValidationErrors) {
            setNotification({
                type: "error",
                title: "Data Tidak Valid",
                message: "Mohon perbaiki kesalahan pada form",
            });
            setIsSubmitting(false);
            return;
        }

        // Prepare clean data for submission
        const cleanData = { ...data };

        // Handle jenis_kelamin conversion
        if (cleanData.jenis_kelamin) {
            console.log(
                "Converting jenis_kelamin from:",
                cleanData.jenis_kelamin
            );
            cleanData.jenis_kelamin =
                cleanData.jenis_kelamin === "Laki-laki" ? "L" : "P";
            console.log("Converted jenis_kelamin to:", cleanData.jenis_kelamin);
        }

        // Remove empty strings dan convert ke null jika perlu
        Object.keys(cleanData).forEach((key) => {
            if (
                cleanData[key] === "" ||
                cleanData[key] === null ||
                cleanData[key] === undefined
            ) {
                cleanData[key] = null;
            }
        });

        // UPDATED: Ensure required fields are not null - NIK REMOVED
        const requiredFieldsForBackend = [
            // REMOVED: "nik" - NIK is now optional
            "nip",
            "nama_lengkap",
            "jenis_kelamin",
            "unit_organisasi",
            "unit_id",
            "nama_jabatan",
            "kelompok_jabatan",
            "status_pegawai",
        ];

        requiredFieldsForBackend.forEach((field) => {
            if (!cleanData[field] || cleanData[field] === null) {
                cleanData[field] = data[field] || "";
            }
        });

        console.log("Clean Data for Submission:", cleanData);

        put(route("employees.update", employee.id), cleanData, {
            onSuccess: (response) => {
                console.log("Form updated successfully:", response);
                setNotification({
                    type: "success",
                    title: "Berhasil!",
                    message: "Data karyawan berhasil diperbarui!",
                });

                setIsSubmitting(false);

                // Redirect after 2 seconds
                setTimeout(() => {
                    router.visit(route("employees.index"));
                }, 2000);
            },
            onError: (errors) => {
                console.error("Form submission errors:", errors);
                setIsSubmitting(false);

                let errorMessage = "Terjadi kesalahan saat memperbarui data";

                if (errors.nik) {
                    errorMessage =
                        "NIK sudah digunakan atau tidak valid (harus 16 digit angka)";
                } else if (errors.nip) {
                    errorMessage =
                        "NIP sudah digunakan atau tidak valid (minimal 5 digit angka)";
                } else if (errors.email) {
                    errorMessage = "Email sudah digunakan atau tidak valid";
                } else if (Object.keys(errors).length > 0) {
                    const firstError = Object.values(errors)[0];
                    errorMessage =
                        typeof firstError === "string"
                            ? firstError
                            : Array.isArray(firstError)
                            ? firstError[0]
                            : "Data yang diisi tidak valid. Silakan periksa kembali.";
                }

                setNotification({
                    type: "error",
                    title: "Gagal Memperbarui",
                    message: errorMessage,
                });

                // Focus ke field pertama yang error
                const firstErrorField = Object.keys(errors)[0];
                if (firstErrorField) {
                    setTimeout(() => {
                        const element = document.querySelector(
                            `[name="${firstErrorField}"]`
                        );
                        if (element) {
                            element.focus();
                            element.scrollIntoView({
                                behavior: "smooth",
                                block: "center",
                            });
                        }
                    }, 100);
                }
            },
            onFinish: () => {
                console.log("Form submission finished");
                setIsSubmitting(false);
            },
            preserveScroll: true,
            preserveState: true,
        });
    };

    const handleCancel = () => {
        const hasChanges = Object.keys(data).some((key) => {
            const originalValue = employee?.[key] || "";
            const currentValue = data[key] || "";

            // Special handling for jenis_kelamin conversion
            if (key === "jenis_kelamin") {
                const originalFormatted =
                    convertJenisKelaminToDisplay(originalValue);
                return originalFormatted !== currentValue;
            }

            return originalValue.toString() !== currentValue.toString();
        });

        if (hasChanges) {
            if (
                confirm(
                    "Yakin ingin membatalkan? Perubahan yang belum disimpan akan hilang."
                )
            ) {
                router.visit(route("employees.index"));
            }
        } else {
            router.visit(route("employees.index"));
        }
    };

    // UPDATED: Render sections - NIK made optional
    const renderPersonalSection = () => (
        <div className="space-y-6">
            <h2 className="flex items-center gap-2 text-lg font-bold text-gray-900">
                <User className="w-5 h-5 text-[#439454]" />
                Data Pribadi
            </h2>

            <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                <InputField
                    name="nik"
                    label="NIK"
                    required={false} // UPDATED: Changed from true to false
                    placeholder="Contoh: 1234567890123456"
                    icon={User}
                    value={data.nik}
                    onChange={handleInputChange}
                    onBlur={handleInputBlur}
                    error={errors.nik || formValidation.nik}
                    hint="Optional - Kosongkan jika belum ada" // UPDATED: Changed hint text
                />
                <InputField
                    name="nip"
                    label="NIP"
                    required={true}
                    placeholder="Contoh: 2024001"
                    icon={User}
                    value={data.nip}
                    onChange={handleInputChange}
                    onBlur={handleInputBlur}
                    error={errors.nip || formValidation.nip}
                    hint="Nomor Induk Pegawai (minimal 5 digit) - WAJIB DIISI"
                />
                <InputField
                    name="nama_lengkap"
                    label="Nama Lengkap"
                    required={true}
                    placeholder="Nama lengkap karyawan"
                    icon={User}
                    value={data.nama_lengkap}
                    onChange={handleInputChange}
                    error={errors.nama_lengkap || formValidation.nama_lengkap}
                />
                <InputField
                    name="jenis_kelamin"
                    label="Jenis Kelamin"
                    required={true}
                    options={["Laki-laki", "Perempuan"]}
                    icon={User}
                    value={data.jenis_kelamin}
                    onChange={handleInputChange}
                    error={errors.jenis_kelamin || formValidation.jenis_kelamin}
                />
                <InputField
                    name="tempat_lahir"
                    label="Tempat Lahir"
                    placeholder="Kota tempat lahir"
                    icon={MapPin}
                    value={data.tempat_lahir}
                    onChange={handleInputChange}
                    error={errors.tempat_lahir}
                />
                <InputField
                    name="tanggal_lahir"
                    label="Tanggal Lahir"
                    type="date"
                    icon={Calendar}
                    value={data.tanggal_lahir}
                    onChange={handleInputChange}
                    onBlur={handleInputBlur}
                    error={errors.tanggal_lahir || formValidation.tanggal_lahir}
                    hint={`TMT Pensiun akan otomatis dihitung (56 tahun dengan logika baru)${
                        calculatedAge
                            ? ` - Usia saat ini: ${calculatedAge} tahun`
                            : ""
                    }`}
                />
                <InputField
                    name="kota_domisili"
                    label="Kota Domisili"
                    placeholder="Kota tempat tinggal saat ini"
                    icon={MapPin}
                    value={data.kota_domisili}
                    onChange={handleInputChange}
                    error={errors.kota_domisili}
                />
                <div className="md:col-span-2">
                    <InputField
                        name="alamat"
                        label="Alamat Lengkap"
                        placeholder="Alamat lengkap tempat tinggal"
                        icon={MapPin}
                        value={data.alamat}
                        onChange={handleInputChange}
                        error={errors.alamat}
                    />
                </div>
                <InputField
                    name="handphone"
                    label="No. Handphone"
                    placeholder="0812-3456-7890"
                    icon={Phone}
                    value={data.handphone}
                    onChange={handleInputChange}
                    onBlur={handleInputBlur}
                    error={errors.handphone || formValidation.handphone}
                />
                <InputField
                    name="email"
                    label="Email"
                    type="email"
                    placeholder="nama@email.com"
                    icon={Mail}
                    value={data.email}
                    onChange={handleInputChange}
                    onBlur={handleInputBlur}
                    error={errors.email || formValidation.email}
                />
            </div>

            {/* Data BPJS Section */}
            <div className="space-y-4">
                <h3 className="flex items-center gap-2 text-base font-semibold text-gray-800">
                    <Shield className="w-4 h-4 text-[#439454]" />
                    Data BPJS
                </h3>
                <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <InputField
                        name="no_bpjs_kesehatan"
                        label="No. BPJS Kesehatan"
                        placeholder="0001234567890"
                        icon={Shield}
                        value={data.no_bpjs_kesehatan}
                        onChange={handleInputChange}
                        error={errors.no_bpjs_kesehatan}
                    />
                    <InputField
                        name="no_bpjs_ketenagakerjaan"
                        label="No. BPJS Ketenagakerjaan"
                        placeholder="0001234567890"
                        icon={Shield}
                        value={data.no_bpjs_ketenagakerjaan}
                        onChange={handleInputChange}
                        error={errors.no_bpjs_ketenagakerjaan}
                    />
                </div>
            </div>
        </div>
    );

    // UPDATED: renderWorkSection dengan format unit display yang baru
    const renderWorkSection = () => {
        // Determine if sub unit is required
        const isSubUnitRequired =
            data.unit_organisasi &&
            !unitWithoutSubUnits.includes(data.unit_organisasi);

        return (
            <div className="space-y-6">
                <h2 className="flex items-center gap-2 text-lg font-bold text-gray-900">
                    <Building2 className="w-5 h-5 text-[#439454]" />
                    Data Pekerjaan & Struktur Organisasi
                </h2>

                {/* Struktur Organisasi Section dengan cascading dropdown */}
                <div className="p-4 border border-blue-200 bg-blue-50 rounded-xl">
                    <h3 className="flex items-center gap-2 mb-4 font-semibold text-blue-800">
                        <Building2 className="w-4 h-4" />
                        Struktur Organisasi (SEMUA WAJIB)
                    </h3>

                    <div className="grid grid-cols-1 gap-6 md:grid-cols-3">
                        <InputField
                            name="unit_organisasi"
                            label="Unit Organisasi"
                            required={true}
                            options={unitOrganisasiOptionsStatic.map(
                                (unit) => ({
                                    value: unit,
                                    label: unit,
                                })
                            )}
                            placeholder="Pilih Unit Organisasi"
                            icon={Building2}
                            value={data.unit_organisasi}
                            onChange={handleInputChange}
                            error={
                                errors.unit_organisasi ||
                                formValidation.unit_organisasi
                            }
                            hint="Pilih unit organisasi terlebih dahulu"
                        />

                        <InputField
                            name="unit_id"
                            label="Unit"
                            required={true}
                            options={availableUnits.map((unit) => ({
                                value: unit.id,
                                label: formatUnitDisplay(
                                    unit,
                                    data.unit_organisasi
                                ),
                            }))}
                            placeholder={
                                loadingUnits
                                    ? "Loading units..."
                                    : availableUnits.length === 0
                                    ? "Pilih unit organisasi dulu"
                                    : "Pilih Unit"
                            }
                            icon={Building2}
                            value={data.unit_id}
                            onChange={handleInputChange}
                            error={errors.unit_id || formValidation.unit_id}
                            disabled={!data.unit_organisasi || loadingUnits}
                            hint={
                                availableUnits.length === 0
                                    ? "Unit akan muncul setelah memilih unit organisasi"
                                    : null
                            }
                        />

                        <InputField
                            name="sub_unit_id"
                            label="Sub Unit"
                            required={isSubUnitRequired}
                            options={availableSubUnits.map((subUnit) => ({
                                value: subUnit.id,
                                label: subUnit.name,
                            }))}
                            placeholder={
                                loadingSubUnits
                                    ? "Loading sub units..."
                                    : !data.unit_id
                                    ? "Pilih Unit dulu"
                                    : unitWithoutSubUnits.includes(
                                          data.unit_organisasi
                                      )
                                    ? "Unit ini tidak memiliki sub unit"
                                    : "Pilih Sub Unit"
                            }
                            icon={Building2}
                            value={data.sub_unit_id}
                            onChange={handleInputChange}
                            error={
                                errors.sub_unit_id || formValidation.sub_unit_id
                            }
                            disabled={
                                !data.unit_id ||
                                unitWithoutSubUnits.includes(
                                    data.unit_organisasi
                                ) ||
                                loadingSubUnits
                            }
                            hint={
                                unitWithoutSubUnits.includes(
                                    data.unit_organisasi
                                )
                                    ? "Unit organisasi ini tidak memiliki sub unit"
                                    : "Sub unit akan muncul setelah memilih unit"
                            }
                        />
                    </div>

                    {/* UPDATED: Preview Struktur Organisasi dengan format yang baru */}
                    {data.unit_organisasi && (
                        <div className="p-3 mt-4 border border-green-200 rounded-lg bg-green-50">
                            <h4 className="mb-2 text-sm font-medium text-green-800">
                                Preview Struktur Organisasi:
                            </h4>
                            <div className="flex flex-wrap items-center gap-2 text-sm text-green-700">
                                <span className="px-2 py-1 bg-green-100 rounded">
                                    {data.unit_organisasi}
                                </span>
                                {availableUnits.find(
                                    (u) => u.id == data.unit_id
                                ) && (
                                    <>
                                        <span>→</span>
                                        <span className="px-2 py-1 bg-green-100 rounded">
                                            {formatUnitDisplay(
                                                availableUnits.find(
                                                    (u) => u.id == data.unit_id
                                                ),
                                                data.unit_organisasi
                                            )}
                                        </span>
                                    </>
                                )}
                                {isSubUnitRequired ? (
                                    availableSubUnits.find(
                                        (u) => u.id == data.sub_unit_id
                                    ) ? (
                                        <>
                                            <span>→</span>
                                            <span className="px-2 py-1 bg-green-100 rounded">
                                                {
                                                    availableSubUnits.find(
                                                        (u) =>
                                                            u.id ==
                                                            data.sub_unit_id
                                                    )?.name
                                                }
                                            </span>
                                        </>
                                    ) : (
                                        <span className="flex items-center gap-1 text-orange-600">
                                            <AlertCircle className="w-3 h-3" />
                                            Sub unit belum dipilih
                                        </span>
                                    )
                                ) : (
                                    <span className="flex items-center gap-1 text-blue-600">
                                        <Info className="w-3 h-3" />
                                        Struktur lengkap untuk{" "}
                                        {data.unit_organisasi}
                                    </span>
                                )}
                            </div>
                        </div>
                    )}
                </div>

                {/* Data Jabatan */}
                <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <InputField
                        name="nama_jabatan"
                        label="Nama Jabatan"
                        required={true}
                        placeholder="Contoh: Manager Operasional"
                        icon={Users}
                        value={data.nama_jabatan}
                        onChange={handleInputChange}
                        error={
                            errors.nama_jabatan || formValidation.nama_jabatan
                        }
                    />
                    <InputField
                        name="status_pegawai"
                        label="Status Pegawai"
                        required={true}
                        options={
                            statusPegawaiOptions.length > 0
                                ? statusPegawaiOptions
                                : [
                                      "PEGAWAI TETAP",
                                      "PKWT",
                                      "TAD PAKET SDM",
                                      "TAD PAKET PEKERJAAN",
                                  ]
                        }
                        placeholder="Pilih Status Pegawai"
                        icon={UserCheck}
                        value={data.status_pegawai}
                        onChange={handleInputChange}
                        error={
                            errors.status_pegawai ||
                            formValidation.status_pegawai
                        }
                    />
                    <InputField
                        name="kelompok_jabatan"
                        label="Kelompok Jabatan"
                        required={true}
                        options={
                            kelompokJabatanOptions.length > 0
                                ? kelompokJabatanOptions
                                : [
                                      "ACCOUNT EXECUTIVE/AE",
                                      "EXECUTIVE GENERAL MANAGER",
                                      "GENERAL MANAGER",
                                      "MANAGER",
                                      "STAFF",
                                      "SUPERVISOR",
                                      "NON",
                                  ]
                        }
                        icon={Users}
                        value={data.kelompok_jabatan}
                        onChange={handleInputChange}
                        error={
                            errors.kelompok_jabatan ||
                            formValidation.kelompok_jabatan
                        }
                    />

                    {/* NEW FIELD: Provider */}
                    <InputField
                        name="provider"
                        label="Provider"
                        options={
                            providerOptions.length > 0
                                ? providerOptions
                                : providerOptionsDefault
                        }
                        placeholder="Pilih Provider"
                        icon={Building2}
                        value={data.provider}
                        onChange={handleInputChange}
                        error={errors.provider}
                    />
                </div>

                {/* Date Fields Section */}
                <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <InputField
                        name="tmt_mulai_kerja"
                        label="TMT Mulai Kerja"
                        type="date"
                        icon={Calendar}
                        value={data.tmt_mulai_kerja}
                        onChange={handleInputChange}
                        error={errors.tmt_mulai_kerja}
                        hint="Tanggal Mulai Tugas pertama kali bekerja"
                    />

                    <InputField
                        name="tmt_berakhir_kerja"
                        label="TMT Berakhir Kerja"
                        type="date"
                        icon={Calendar}
                        value={data.tmt_berakhir_kerja}
                        onChange={handleInputChange}
                        onBlur={handleInputBlur}
                        error={
                            errors.tmt_berakhir_kerja ||
                            formValidation.tmt_berakhir_kerja
                        }
                        hint="Harus diisi setelah TMT Mulai Kerja diisi"
                    />

                    <InputField
                        name="tmt_mulai_jabatan"
                        label="TMT Mulai Jabatan"
                        type="date"
                        icon={Calendar}
                        value={data.tmt_mulai_jabatan}
                        onChange={handleInputChange}
                        error={errors.tmt_mulai_jabatan}
                        hint="Tanggal Mulai Tugas pada jabatan saat ini"
                    />

                    {/* NEW FIELD: TMT Akhir Jabatan */}
                    <InputField
                        name="tmt_akhir_jabatan"
                        label="TMT Akhir Jabatan"
                        type="date"
                        icon={Calendar}
                        value={data.tmt_akhir_jabatan}
                        onChange={handleInputChange}
                        onBlur={handleInputBlur}
                        error={
                            errors.tmt_akhir_jabatan ||
                            formValidation.tmt_akhir_jabatan
                        }
                        hint="Harus diisi setelah TMT Mulai Jabatan diisi"
                    />
                </div>

                {/* Additional Work Information */}
                <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                    {/* NEW FIELD: Status Kerja - EDITABLE in Edit Mode */}
                    <InputField
                        name="status_kerja"
                        label="Status Kerja"
                        options={
                            statusKerjaOptions.length > 0
                                ? statusKerjaOptions
                                : statusKerjaOptionsDefault
                        }
                        placeholder="Pilih Status Kerja"
                        value={data.status_kerja}
                        onChange={handleInputChange}
                        error={errors.status_kerja}
                        hint="Bisa diubah manual: Aktif, Non-Aktif, Pensiun, Mutasi"
                        icon={UserCheck}
                    />

                    {/* NEW FIELD: Unit Kerja Kontrak */}
                    <InputField
                        name="unit_kerja_kontrak"
                        label="Unit Kerja Sesuai Kontrak"
                        placeholder="Contoh: ADMINISTRASI"
                        icon={Building2}
                        value={data.unit_kerja_kontrak}
                        onChange={handleInputChange}
                        onBlur={handleInputBlur}
                        error={
                            errors.unit_kerja_kontrak ||
                            formValidation.unit_kerja_kontrak
                        }
                    />

                    {/* NEW FIELD: Grade */}
                    <InputField
                        name="grade"
                        label="Grade"
                        placeholder="Contoh: IX"
                        icon={Users}
                        value={data.grade}
                        onChange={handleInputChange}
                        onBlur={handleInputBlur}
                        error={errors.grade || formValidation.grade}
                    />

                    {/* NEW FIELD: Lokasi Kerja - Read Only */}
                    <InputField
                        name="lokasi_kerja"
                        label="Lokasi Kerja"
                        value={data.lokasi_kerja}
                        onChange={handleInputChange}
                        readonly={true}
                        icon={MapPin}
                        hint="Otomatis terisi Bandar Udara Ngurah Rai"
                    />

                    {/* NEW FIELD: Cabang - Read Only */}
                    <InputField
                        name="cabang"
                        label="Cabang"
                        value={data.cabang}
                        onChange={handleInputChange}
                        readonly={true}
                        icon={MapPin}
                        hint="Otomatis terisi DPS"
                    />

                    {/* FIXED: Masa Kerja - Read Only with corrected calculation */}
                    <InputField
                        name="masa_kerja"
                        label="Masa Kerja"
                        value={data.masa_kerja}
                        onChange={handleInputChange}
                        readonly={true}
                        icon={Calendar}
                        hint="Otomatis dihitung berdasarkan TMT Mulai Kerja sampai TMT Berakhir Kerja (jika ada) atau hari ini"
                    />
                </div>

                {/* Existing TMT Pensiun field */}
                <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <InputField
                        name="tmt_pensiun"
                        label="TMT Pensiun"
                        type="date"
                        icon={Calendar}
                        value={data.tmt_pensiun}
                        onChange={handleInputChange}
                        error={errors.tmt_pensiun}
                        hint="Otomatis dihitung dari tanggal lahir (56 tahun dengan logika baru)"
                        disabled={true}
                    />
                </div>
            </div>
        );
    };

    const renderEducationSection = () => (
        <div className="space-y-6">
            <h2 className="flex items-center gap-2 text-lg font-bold text-gray-900">
                <GraduationCap className="w-5 h-5 text-[#439454]" />
                Pendidikan
            </h2>

            <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                <InputField
                    name="pendidikan_terakhir"
                    label="Pendidikan Terakhir"
                    options={[
                        "SD",
                        "SMP",
                        "SMA",
                        "SMK",
                        "D1",
                        "D2",
                        "D3",
                        "S1",
                        "S2",
                        "S3",
                    ]}
                    placeholder="Pilih Pendidikan Terakhir"
                    icon={GraduationCap}
                    value={data.pendidikan_terakhir}
                    onChange={handleInputChange}
                    error={errors.pendidikan_terakhir}
                />
                <InputField
                    name="instansi_pendidikan"
                    label="Instansi Pendidikan"
                    placeholder="Nama sekolah/universitas"
                    icon={Building2}
                    value={data.instansi_pendidikan}
                    onChange={handleInputChange}
                    error={errors.instansi_pendidikan}
                />
                <InputField
                    name="jurusan"
                    label="Jurusan"
                    placeholder="Nama jurusan/program studi"
                    icon={GraduationCap}
                    value={data.jurusan}
                    onChange={handleInputChange}
                    error={errors.jurusan}
                />
                <InputField
                    name="tahun_lulus"
                    label="Tahun Lulus"
                    type="number"
                    placeholder="2020"
                    icon={Calendar}
                    value={data.tahun_lulus}
                    onChange={handleInputChange}
                    onBlur={handleInputBlur}
                    error={errors.tahun_lulus || formValidation.tahun_lulus}
                    hint="Tahun kelulusan dari pendidikan terakhir"
                />
            </div>
        </div>
    );

    const renderAdditionalSection = () => (
        <div className="space-y-6">
            <h2 className="flex items-center gap-2 text-lg font-bold text-gray-900">
                <FileText className="w-5 h-5 text-[#439454]" />
                Data Tambahan
            </h2>

            <div className="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                <InputField
                    name="seragam"
                    label="Seragam"
                    placeholder="Akan diisi nanti"
                    icon={Shield}
                    value={data.seragam}
                    onChange={handleInputChange}
                    readonly={true}
                    error={errors.seragam}
                    hint="Field ini akan diisi oleh admin"
                />
                <InputField
                    name="jenis_sepatu"
                    label="Jenis Sepatu"
                    options={["Pantofel", "Safety Shoes"]}
                    placeholder="Pilih Jenis Sepatu"
                    icon={Briefcase}
                    value={data.jenis_sepatu}
                    onChange={handleInputChange}
                    error={errors.jenis_sepatu}
                    hint="Jenis sepatu kerja yang digunakan"
                />
                <InputField
                    name="ukuran_sepatu"
                    label="Ukuran Sepatu"
                    options={[
                        "36",
                        "37",
                        "38",
                        "39",
                        "40",
                        "41",
                        "42",
                        "43",
                        "44",
                        "45",
                        "46",
                    ]}
                    placeholder="Pilih Ukuran Sepatu"
                    icon={Briefcase}
                    value={data.ukuran_sepatu}
                    onChange={handleInputChange}
                    onBlur={handleInputBlur}
                    error={errors.ukuran_sepatu || formValidation.ukuran_sepatu}
                    hint="Ukuran sepatu karyawan (30-50)"
                />
                <InputField
                    name="height"
                    label="Tinggi Badan (cm)"
                    type="number"
                    placeholder="170"
                    icon={User}
                    value={data.height}
                    onChange={handleInputChange}
                    onBlur={handleInputBlur}
                    error={errors.height || formValidation.height}
                    hint="Tinggi badan dalam centimeter"
                />
                <InputField
                    name="weight"
                    label="Berat Badan (kg)"
                    type="number"
                    placeholder="70"
                    icon={User}
                    value={data.weight}
                    onChange={handleInputChange}
                    onBlur={handleInputBlur}
                    error={errors.weight || formValidation.weight}
                    hint="Berat badan dalam kilogram"
                />
            </div>
        </div>
    );

    const renderSection = () => {
        switch (activeSection) {
            case "personal":
                return renderPersonalSection();
            case "work":
                return renderWorkSection();
            case "education":
                return renderEducationSection();
            case "additional":
                return renderAdditionalSection();
            default:
                return renderPersonalSection();
        }
    };

    return (
        <DashboardLayout>
            <Head
                title={`Edit Karyawan - ${employee?.nama_lengkap || "Unknown"}`}
            />

            {/* Enhanced Notification */}
            {notification && (
                <FormNotification
                    type={notification.type}
                    title={notification.title}
                    message={notification.message}
                    onClose={() => setNotification(null)}
                />
            )}

            <div className="p-6 space-y-6">
                {/* Header */}
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div className="flex items-center gap-4">
                        <button
                            onClick={handleCancel}
                            disabled={processing || isSubmitting}
                            className="flex items-center gap-2 px-4 py-2 text-gray-600 transition-all duration-300 border-2 border-gray-300 rounded-xl hover:border-[#439454] hover:text-[#439454] focus:ring-4 focus:ring-[#439454]/20 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <ArrowLeft className="w-4 h-4" />
                            Kembali
                        </button>
                        <div>
                            <h1 className="text-2xl font-bold text-gray-900">
                                Edit Karyawan
                            </h1>
                            <p className="text-sm text-gray-600">
                                Perbarui data karyawan{" "}
                                {employee?.nama_lengkap || "Unknown"}. Lengkapi
                                data karyawan dengan teliti, SEMUA struktur
                                organisasi (Unit Organisasi, Unit, Sub Unit)
                                WAJIB diisi.
                            </p>
                        </div>
                    </div>
                </div>

                {/* Section Navigation */}
                <div className="flex flex-wrap gap-2 p-2 bg-gray-100 rounded-xl">
                    {Object.entries(sections).map(([key, section]) => (
                        <button
                            key={key}
                            onClick={() => setActiveSection(key)}
                            disabled={processing || isSubmitting}
                            className={`flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed ${
                                activeSection === key
                                    ? "bg-[#439454] text-white shadow-lg"
                                    : "text-gray-600 hover:bg-white hover:text-[#439454]"
                            }`}
                        >
                            <section.icon className="w-4 h-4" />
                            {section.name}
                        </button>
                    ))}
                </div>

                {/* Form */}
                <form
                    onSubmit={handleSubmit}
                    className="bg-white border-2 border-gray-200 shadow-xl rounded-2xl"
                >
                    <div className="p-6 space-y-6">{renderSection()}</div>

                    {/* Form Actions */}
                    <div className="flex flex-col gap-4 p-6 border-t border-gray-200 sm:flex-row sm:items-center sm:justify-end bg-gray-50 rounded-b-2xl">
                        <button
                            type="button"
                            onClick={handleCancel}
                            disabled={processing || isSubmitting}
                            className="flex items-center justify-center gap-2 px-6 py-3 text-gray-600 transition-all duration-300 border-2 border-gray-300 rounded-xl hover:border-red-400 hover:text-red-600 focus:ring-4 focus:ring-red-400/20 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <X className="w-4 h-4" />
                            Batal
                        </button>
                        <button
                            type="submit"
                            disabled={processing || isSubmitting}
                            className="flex items-center justify-center gap-2 px-6 py-3 text-white transition-all duration-300 bg-[#439454] border-2 border-[#439454] rounded-xl hover:bg-[#439454]/90 focus:ring-4 focus:ring-[#439454]/20 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            {processing || isSubmitting ? (
                                <>
                                    <Loader2 className="w-4 h-4 animate-spin" />
                                    Menyimpan...
                                </>
                            ) : (
                                <>
                                    <Save className="w-4 h-4" />
                                    Perbarui Karyawan
                                </>
                            )}
                        </button>
                    </div>
                </form>
            </div>
        </DashboardLayout>
    );
}
