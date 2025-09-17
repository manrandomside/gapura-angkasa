import React, { useState, useEffect } from "react";
import { Head, useForm, router } from "@inertiajs/react";
import DashboardLayout from "@/Layouts/DashboardLayout";
import {
    Save,
    X,
    User,
    Phone,
    Mail,
    MapPin,
    Calendar,
    GraduationCap,
    Building2,
    UserCheck,
    AlertCircle,
    CheckCircle,
    ArrowLeft,
    FileText,
    Shield,
    Loader2,
    Info,
    AlertTriangle,
    Users,
    Briefcase,
} from "lucide-react";

// Enhanced InputField component dengan better validation dan styling
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
    readonly = false,
    hint = null,
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
            </label>

            {options ? (
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
                    {options.map((option) => (
                        <option
                            key={option.value || option}
                            value={option.value || option}
                        >
                            {option.label || option}
                        </option>
                    ))}
                </select>
            ) : type === "textarea" ? (
                <textarea
                    name={name}
                    value={value || ""}
                    onChange={handleChange}
                    onBlur={handleBlur}
                    onFocus={handleFocus}
                    placeholder={placeholder}
                    disabled={disabled}
                    readOnly={readonly}
                    rows={4}
                    className={`${getInputClasses()} resize-none`}
                />
            ) : (
                <input
                    type={type}
                    name={name}
                    value={value || ""}
                    onChange={handleChange}
                    onBlur={handleBlur}
                    onFocus={handleFocus}
                    placeholder={placeholder}
                    disabled={disabled}
                    readOnly={readonly}
                    className={getInputClasses()}
                />
            )}

            {hint && !error && (
                <div className="flex items-center gap-2 text-xs text-gray-500">
                    <Info className="w-3 h-3" />
                    {hint}
                </div>
            )}

            {error && (
                <div className="flex items-center gap-2 text-sm text-red-600">
                    <AlertCircle className="w-4 h-4" />
                    {error}
                </div>
            )}
        </div>
    );
};

// Enhanced notification component untuk form feedback
const FormNotification = ({ type, title, message, onClose }) => {
    const [visible, setVisible] = useState(true);

    useEffect(() => {
        if (type === "success") {
            const timer = setTimeout(() => {
                setVisible(false);
                setTimeout(onClose, 300);
            }, 3000);
            return () => clearTimeout(timer);
        }
    }, [type, onClose]);

    if (!visible) return null;

    const getIcon = () => {
        switch (type) {
            case "success":
                return <CheckCircle className="w-5 h-5 text-green-600" />;
            case "error":
                return <AlertTriangle className="w-5 h-5 text-red-600" />;
            default:
                return <Info className="w-5 h-5 text-blue-600" />;
        }
    };

    const getBgColor = () => {
        switch (type) {
            case "success":
                return "bg-green-50 border-green-200";
            case "error":
                return "bg-red-50 border-red-200";
            default:
                return "bg-blue-50 border-blue-200";
        }
    };

    return (
        <div
            className={`fixed top-4 right-4 z-50 max-w-md p-4 border-2 rounded-xl shadow-lg transition-all duration-300 ${getBgColor()} ${
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

export default function Create({
    organizations = [],
    unitOptions = [],
    jabatanOptions = [],
    statusPegawaiOptions = [],
    kelompokJabatanOptions = [],
    unitOrganisasiOptions = [],
    providerOptions = [],
    statusKerjaOptions = [],
    success = null,
    error = null,
    message = null,
}) {
    const { data, setData, post, processing, errors, reset, clearErrors } =
        useForm({
            // Existing fields
            nik: "",
            nip: "",
            nama_lengkap: "",
            jenis_kelamin: "",
            tempat_lahir: "",
            tanggal_lahir: "",
            alamat: "",
            kota_domisili: "",
            handphone: "",
            email: "",
            no_bpjs_kesehatan: "",
            no_bpjs_ketenagakerjaan: "",
            unit_organisasi: "",
            unit_id: "",
            sub_unit_id: "",
            jabatan: "",
            nama_jabatan: "",
            status_pegawai: "",
            kelompok_jabatan: "",
            tmt_mulai_jabatan: "",
            tmt_mulai_kerja: "",
            tmt_pensiun: "",
            pendidikan_terakhir: "",
            pendidikan: "",
            instansi_pendidikan: "",
            jurusan: "",
            tahun_lulus: "",
            jenis_sepatu: "",
            ukuran_sepatu: "",
            height: "",
            weight: "",
            seragam: "",

            // NEW FIELDS
            status_kerja: "Non-Aktif", // Auto-calculated
            tmt_akhir_jabatan: "",
            provider: "",
            unit_kerja_kontrak: "",
            grade: "",
            lokasi_kerja: "Bandar Udara Ngurah Rai", // Fixed value
            cabang: "DPS", // Fixed value
            masa_kerja: "", // Auto-calculated
            tmt_berakhir_kerja: "",
        });

    // FIXED: Jenis kelamin options dengan format yang user-friendly
    const jenisKelaminOptions = [
        { value: "Laki-laki", label: "Laki-laki" },
        { value: "Perempuan", label: "Perempuan" },
    ];

    // Provider options sesuai permintaan Anda
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

    const [activeSection, setActiveSection] = useState("personal");
    const [formValidation, setFormValidation] = useState({});
    const [notification, setNotification] = useState(null);
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [calculatedAge, setCalculatedAge] = useState(null);

    // State untuk cascading dropdown
    const [availableUnits, setAvailableUnits] = useState([]);
    const [availableSubUnits, setAvailableSubUnits] = useState([]);
    const [loadingUnits, setLoadingUnits] = useState(false);
    const [loadingSubUnits, setLoadingSubUnits] = useState(false);

    // Unit organisasi yang tidak memiliki sub unit
    const unitWithoutSubUnits = ["EGM", "GM"];

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

    // FIXED: Calculate masa kerja from TMT mulai kerja hingga TMT berakhir kerja (atau hari ini jika belum berakhir)
    const calculateMasaKerja = (startDate, endDate = null) => {
        if (!startDate) return "";

        const start = new Date(startDate);
        const end = endDate ? new Date(endDate) : new Date(); // Gunakan endDate jika ada, atau hari ini

        let years = end.getFullYear() - start.getFullYear();
        let months = end.getMonth() - start.getMonth();

        if (months < 0) {
            years--;
            months += 12;
        }

        // Adjust untuk tanggal yang belum lewat di bulan ini
        if (end.getDate() < start.getDate()) {
            months--;
            if (months < 0) {
                years--;
                months += 12;
            }
        }

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

    // Calculate status kerja based on tmt_berakhir_kerja
    const calculateStatusKerja = (berakhirKerja) => {
        if (!berakhirKerja) return "Non-Aktif";

        const today = new Date();
        const endDate = new Date(berakhirKerja);

        return today <= endDate ? "Aktif" : "Non-Aktif";
    };

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
        }
    }, [success, error]);

    // FIXED: Auto-calculate masa kerja when tmt_mulai_kerja OR tmt_berakhir_kerja changes
    useEffect(() => {
        if (data.tmt_mulai_kerja) {
            const masaKerja = calculateMasaKerja(
                data.tmt_mulai_kerja,
                data.tmt_berakhir_kerja
            );
            setData("masa_kerja", masaKerja);
        } else {
            setData("masa_kerja", "");
        }
    }, [data.tmt_mulai_kerja, data.tmt_berakhir_kerja]); // FIXED: Tambahkan tmt_berakhir_kerja sebagai dependency

    // Auto-calculate status kerja when tmt_berakhir_kerja changes
    useEffect(() => {
        if (data.tmt_berakhir_kerja) {
            const statusKerja = calculateStatusKerja(data.tmt_berakhir_kerja);
            setData("status_kerja", statusKerja);
        } else {
            setData("status_kerja", "Non-Aktif");
        }
    }, [data.tmt_berakhir_kerja]);

    // Fetch units berdasarkan unit organisasi dengan error handling yang lebih baik
    const fetchUnits = async (unitOrganisasi) => {
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

                // Show success notification only if units were found
                if (result.data.length > 0) {
                    setNotification({
                        type: "success",
                        title: "Berhasil!",
                        message: `${result.data.length} unit berhasil dimuat untuk ${unitOrganisasi}`,
                    });
                }
            } else {
                console.warn("No units found for:", unitOrganisasi, result);
                setAvailableUnits([]);

                // Show info notification
                setNotification({
                    type: "info",
                    title: "Informasi",
                    message:
                        result.message ||
                        `Tidak ada unit tersedia untuk ${unitOrganisasi}`,
                });
            }
        } catch (error) {
            console.error("Error fetching units:", error);
            setAvailableUnits([]);

            // Show error notification
            setNotification({
                type: "error",
                title: "Kesalahan!",
                message: `Gagal memuat unit untuk ${unitOrganisasi}: ${error.message}`,
            });
        } finally {
            setLoadingUnits(false);
        }
    };

    // Fetch sub units berdasarkan unit_id dengan error handling yang lebih baik
    const fetchSubUnits = async (unitId) => {
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

                if (result.data.length > 0) {
                    // Show success notification for sub units
                    setNotification({
                        type: "success",
                        title: "Berhasil!",
                        message: `${result.data.length} sub unit berhasil dimuat`,
                    });
                }
            } else {
                console.warn("No sub units found for unit_id:", unitId, result);
                setAvailableSubUnits([]);

                // Only show notification if it's an actual error, not when unit doesn't have sub units
                if (result.success === false) {
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

            // Show error notification
            setNotification({
                type: "error",
                title: "Kesalahan!",
                message: `Gagal memuat sub unit: ${error.message}`,
            });
        } finally {
            setLoadingSubUnits(false);
        }
    };

    // Auto-calculate pension date dengan logika baru (56 TAHUN)
    useEffect(() => {
        if (data.tanggal_lahir) {
            const birthDate = new Date(data.tanggal_lahir);
            const pensionDate = new Date(birthDate);

            // Logika TMT Pensiun berdasarkan aturan baru
            // Jika lahir dibawah tanggal 10: pensiun 1 pada bulan yang sama
            // Jika lahir diatas tanggal 10: pensiun 1 bulan berikutnya
            pensionDate.setFullYear(birthDate.getFullYear() + 56);

            if (birthDate.getDate() < 10) {
                // Lahir dibawah tanggal 10: pensiun 1 pada bulan yang sama
                pensionDate.setDate(1);
            } else {
                // Lahir diatas tanggal 10: pensiun 1 bulan berikutnya
                pensionDate.setDate(1);
                pensionDate.setMonth(pensionDate.getMonth() + 1);
            }

            setData("tmt_pensiun", pensionDate.toISOString().split("T")[0]);

            // Calculate age
            const today = new Date();
            const age = today.getFullYear() - birthDate.getFullYear();
            const monthDiff = today.getMonth() - birthDate.getMonth();
            const finalAge =
                monthDiff < 0 ||
                (monthDiff === 0 && today.getDate() < birthDate.getDate())
                    ? age - 1
                    : age;
            setCalculatedAge(finalAge);
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
                    error = "Format nomor handphone tidak valid";
                }
                break;
            case "tahun_lulus":
                const currentYear = new Date().getFullYear();
                if (
                    value &&
                    (parseInt(value) < 1950 ||
                        parseInt(value) > currentYear + 5)
                ) {
                    error = `Tahun lulus harus antara 1950 - ${
                        currentYear + 5
                    }`;
                }
                break;
            case "height":
                if (value && (parseInt(value) < 100 || parseInt(value) > 250)) {
                    error = "Tinggi badan harus antara 100-250 cm";
                }
                break;
            case "weight":
                if (value && (parseInt(value) < 30 || parseInt(value) > 200)) {
                    error = "Berat badan harus antara 30-200 kg";
                }
                break;
            case "ukuran_sepatu":
                if (value && (parseInt(value) < 30 || parseInt(value) > 50)) {
                    error = "Ukuran sepatu harus antara 30-50";
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
            // NEW: Validation for new fields
            case "unit_kerja_kontrak":
                if (value && value.length > 255) {
                    error = "Unit kerja kontrak maksimal 255 karakter";
                }
                break;
            case "grade":
                if (value && value.length > 50) {
                    error = "Grade maksimal 50 karakter";
                }
                break;
            case "tmt_akhir_jabatan":
                if (value && data.tmt_mulai_jabatan) {
                    const mulaiJabatan = new Date(data.tmt_mulai_jabatan);
                    const akhirJabatan = new Date(value);

                    if (akhirJabatan <= mulaiJabatan) {
                        error =
                            "TMT Akhir Jabatan harus diatas tanggal TMT Mulai Jabatan";
                    }
                }
                break;
            case "tmt_berakhir_kerja":
                if (value && data.tmt_mulai_kerja) {
                    const mulaiKerja = new Date(data.tmt_mulai_kerja);
                    const berakhirKerja = new Date(value);

                    if (berakhirKerja <= mulaiKerja) {
                        error =
                            "TMT Berakhir Kerja harus diatas tanggal TMT Mulai Kerja";
                    }
                }
                break;
        }

        setFormValidation((prev) => ({
            ...prev,
            [fieldName]: error,
        }));

        // Clear server errors when user starts typing
        if (errors[fieldName]) {
            clearErrors(fieldName);
        }

        return error;
    };

    // ENHANCED: handleInputChange dengan improved cascading logic dan NEW FIELD logic
    const handleInputChange = (name, value) => {
        setData(name, value);

        // Handle cascading dropdown untuk struktur organisasi
        if (name === "unit_organisasi") {
            console.log("Unit organisasi changed to:", value);

            // Reset unit dan sub unit saat unit organisasi berubah
            setData("unit_id", "");
            setData("sub_unit_id", "");
            setAvailableUnits([]);
            setAvailableSubUnits([]);

            // Clear validation errors
            clearErrors("unit_id");
            clearErrors("sub_unit_id");
            setFormValidation((prev) => ({
                ...prev,
                unit_id: "",
                sub_unit_id: "",
            }));

            // Clear sub_unit_id error jika unit organisasi tidak memerlukan sub unit
            if (unitWithoutSubUnits.includes(value)) {
                setFormValidation((prev) => ({
                    ...prev,
                    sub_unit_id: "",
                }));
            }

            // Fetch units untuk unit organisasi yang dipilih
            if (value) {
                fetchUnits(value);
            }
        } else if (name === "unit_id") {
            console.log("Unit ID changed to:", value);

            // Reset sub unit saat unit berubah
            setData("sub_unit_id", "");
            setAvailableSubUnits([]);

            // Clear validation errors
            clearErrors("sub_unit_id");
            setFormValidation((prev) => ({
                ...prev,
                sub_unit_id: "",
            }));

            // Load sub units untuk unit yang dipilih (hanya jika required)
            if (value && !unitWithoutSubUnits.includes(data.unit_organisasi)) {
                fetchSubUnits(value);
            }
        }

        // NEW: Date validation for TMT Akhir Jabatan
        if (name === "tmt_akhir_jabatan") {
            if (value && data.tmt_mulai_jabatan) {
                const mulaiJabatan = new Date(data.tmt_mulai_jabatan);
                const akhirJabatan = new Date(value);

                if (akhirJabatan <= mulaiJabatan) {
                    setFormValidation((prev) => ({
                        ...prev,
                        tmt_akhir_jabatan:
                            "TMT Akhir Jabatan harus diatas tanggal TMT Mulai Jabatan",
                    }));
                } else {
                    setFormValidation((prev) => ({
                        ...prev,
                        tmt_akhir_jabatan: "",
                    }));
                }
            }
        }

        // NEW: Date validation for TMT Berakhir Kerja
        if (name === "tmt_berakhir_kerja") {
            if (value && data.tmt_mulai_kerja) {
                const mulaiKerja = new Date(data.tmt_mulai_kerja);
                const berakhirKerja = new Date(value);

                if (berakhirKerja <= mulaiKerja) {
                    setFormValidation((prev) => ({
                        ...prev,
                        tmt_berakhir_kerja:
                            "TMT Berakhir Kerja harus diatas tanggal TMT Mulai Kerja",
                    }));
                } else {
                    setFormValidation((prev) => ({
                        ...prev,
                        tmt_berakhir_kerja: "",
                    }));
                    // Update status kerja when valid berakhir kerja is set
                    const statusKerja = calculateStatusKerja(value);
                    setData("status_kerja", statusKerja);
                }
            }
        }

        // Clear dependent fields when parent date changes
        if (name === "tmt_mulai_jabatan") {
            if (data.tmt_akhir_jabatan) {
                setData("tmt_akhir_jabatan", "");
                setFormValidation((prev) => ({
                    ...prev,
                    tmt_akhir_jabatan: "",
                }));
            }
        }

        if (name === "tmt_mulai_kerja") {
            if (data.tmt_berakhir_kerja) {
                setData("tmt_berakhir_kerja", "");
                setData("status_kerja", "Non-Aktif");
                setData("masa_kerja", "");
                setFormValidation((prev) => ({
                    ...prev,
                    tmt_berakhir_kerja: "",
                }));
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
                "unit_kerja_kontrak",
                "grade",
                "tmt_akhir_jabatan",
                "tmt_berakhir_kerja",
            ].includes(name)
        ) {
            setTimeout(() => validateField(name, value), 500);
        }
    };

    const handleInputBlur = (name, value) => {
        validateField(name, value);
    };

    // UPDATED: Conditional validation untuk sub_unit_id berdasarkan unit_organisasi - NIK REMOVED FROM REQUIRED
    const validateRequiredFields = () => {
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

        // Conditional sub_unit_id validation
        // Hanya tambahkan sub_unit_id ke required fields jika unit_organisasi bukan EGM atau GM
        if (
            data.unit_organisasi &&
            !unitWithoutSubUnits.includes(data.unit_organisasi)
        ) {
            requiredFields.sub_unit_id = "Sub unit wajib dipilih";
        }

        const newErrors = {};

        Object.entries(requiredFields).forEach(([field, message]) => {
            if (!data[field] || data[field].toString().trim() === "") {
                newErrors[field] = message;
            }
        });

        // Additional validation for date dependencies
        if (data.tmt_akhir_jabatan && !data.tmt_mulai_jabatan) {
            newErrors.tmt_mulai_jabatan =
                "TMT Mulai Jabatan harus diisi terlebih dahulu";
        }

        if (data.tmt_berakhir_kerja && !data.tmt_mulai_kerja) {
            newErrors.tmt_mulai_kerja =
                "TMT Mulai Kerja harus diisi terlebih dahulu";
        }

        return newErrors;
    };

    // Enhanced form submission dengan better error handling
    const handleSubmit = (e) => {
        e.preventDefault();
        setIsSubmitting(true);

        console.log("Form Data Submission:", data);

        // Validate required fields
        const requiredFieldErrors = validateRequiredFields();
        if (Object.keys(requiredFieldErrors).length > 0) {
            setFormValidation((prev) => ({ ...prev, ...requiredFieldErrors }));

            // Conditional notification message
            const isEgmOrGm = unitWithoutSubUnits.includes(
                data.unit_organisasi
            );
            const message = isEgmOrGm
                ? "Mohon lengkapi semua field yang wajib diisi"
                : "Mohon lengkapi semua field yang wajib diisi termasuk struktur organisasi lengkap";

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

        // Clean data before submission - proper format for backend
        const cleanData = { ...data };

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

        // Submit to backend
        post(route("employees.store"), {
            onBefore: () => {
                console.log("Starting form submission...");
            },
            onStart: () => {
                console.log("Form submission started");
            },
            onProgress: (progress) => {
                console.log("Upload progress:", progress);
            },
            onSuccess: (response) => {
                console.log("Form submitted successfully:", response);
                setNotification({
                    type: "success",
                    title: "Berhasil!",
                    message:
                        "Karyawan berhasil ditambahkan dengan struktur organisasi lengkap. Mengalihkan ke daftar karyawan...",
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

                let errorMessage = "Terjadi kesalahan saat menyimpan data";

                if (errors.nik) {
                    errorMessage =
                        "NIK sudah digunakan atau tidak valid (harus 16 digit angka)";
                } else if (errors.nip) {
                    errorMessage =
                        "NIP sudah digunakan atau tidak valid (minimal 5 digit angka)";
                } else if (errors.email) {
                    errorMessage = "Email sudah digunakan atau tidak valid";
                } else if (errors.unit_organisasi) {
                    errorMessage =
                        "Unit organisasi tidak valid atau belum dipilih";
                } else if (errors.unit_id) {
                    errorMessage =
                        "Unit wajib dipilih dan harus sesuai dengan unit organisasi";
                } else if (errors.sub_unit_id) {
                    errorMessage =
                        "Sub unit wajib dipilih dan harus sesuai dengan unit";
                } else if (errors.status_pegawai) {
                    errorMessage = "Status pegawai wajib dipilih";
                } else if (errors.kelompok_jabatan) {
                    errorMessage = "Kelompok jabatan wajib dipilih";
                } else if (errors.provider) {
                    errorMessage = "Provider yang dipilih tidak valid";
                } else if (errors.tmt_akhir_jabatan) {
                    errorMessage =
                        "TMT Akhir Jabatan harus diatas TMT Mulai Jabatan";
                } else if (errors.tmt_berakhir_kerja) {
                    errorMessage =
                        "TMT Berakhir Kerja harus diatas TMT Mulai Kerja";
                } else if (errors.jenis_kelamin) {
                    errorMessage = "Jenis kelamin wajib dipilih";
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
                    title: "Gagal Menyimpan",
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
        const hasData = Object.values(data).some(
            (value) => value && value.toString().trim() !== ""
        );

        if (hasData) {
            if (
                confirm(
                    "Yakin ingin membatalkan? Data yang sudah diisi akan hilang."
                )
            ) {
                router.visit(route("employees.index"));
            }
        } else {
            router.visit(route("employees.index"));
        }
    };

    // UPDATED: renderPersonalSection - NIK made optional
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
                {/* FIXED: Jenis Kelamin dengan options yang user-friendly */}
                <InputField
                    name="jenis_kelamin"
                    label="Jenis Kelamin"
                    required={true}
                    options={jenisKelaminOptions}
                    placeholder="Pilih Jenis Kelamin"
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
                            ? ` - Umur saat ini: ${calculatedAge} tahun`
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
            </div>

            <InputField
                name="alamat"
                label="Alamat Lengkap"
                type="textarea"
                placeholder="Alamat lengkap tempat tinggal"
                icon={MapPin}
                value={data.alamat}
                onChange={handleInputChange}
                error={errors.alamat}
            />

            <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
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

            {/* BPJS Fields */}
            <div className="pt-4 border-t border-gray-200">
                <h3 className="flex items-center gap-2 mb-4 font-semibold text-gray-800 text-md">
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
                            options={unitOrganisasiOptions.map((unit) => ({
                                value: unit,
                                label: unit,
                            }))}
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
                                value: unit.id || unit.value,
                                label: formatUnitDisplay(
                                    unit,
                                    data.unit_organisasi
                                ),
                            }))}
                            placeholder={
                                loadingUnits
                                    ? "Loading units..."
                                    : availableUnits.length > 0
                                    ? "Pilih Unit"
                                    : "Pilih Unit Organisasi dulu"
                            }
                            icon={Building2}
                            value={data.unit_id}
                            onChange={handleInputChange}
                            disabled={!data.unit_organisasi || loadingUnits}
                            error={errors.unit_id || formValidation.unit_id}
                            hint="Unit akan muncul setelah memilih unit organisasi"
                        />

                        <InputField
                            name="sub_unit_id"
                            label="Sub Unit"
                            required={isSubUnitRequired}
                            options={availableSubUnits.map((subUnit) => ({
                                value: subUnit.id || subUnit.value,
                                label: subUnit.label || subUnit.name,
                            }))}
                            placeholder={
                                loadingSubUnits
                                    ? "Loading sub units..."
                                    : unitWithoutSubUnits.includes(
                                          data.unit_organisasi
                                      )
                                    ? "Tidak ada sub unit untuk unit organisasi ini"
                                    : availableSubUnits.length > 0
                                    ? "Pilih Sub Unit"
                                    : "Pilih Unit dulu"
                            }
                            icon={Building2}
                            value={data.sub_unit_id}
                            onChange={handleInputChange}
                            disabled={
                                !data.unit_id ||
                                loadingSubUnits ||
                                unitWithoutSubUnits.includes(
                                    data.unit_organisasi
                                )
                            }
                            error={
                                errors.sub_unit_id || formValidation.sub_unit_id
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
                                    (u) => (u.id || u.value) == data.unit_id
                                ) && (
                                    <>
                                        <span>→</span>
                                        <span className="px-2 py-1 bg-green-100 rounded">
                                            {formatUnitDisplay(
                                                availableUnits.find(
                                                    (u) =>
                                                        (u.id || u.value) ==
                                                        data.unit_id
                                                ),
                                                data.unit_organisasi
                                            )}
                                        </span>
                                    </>
                                )}
                                {isSubUnitRequired ? (
                                    availableSubUnits.find(
                                        (su) =>
                                            (su.id || su.value) ==
                                            data.sub_unit_id
                                    ) && (
                                        <>
                                            <span>→</span>
                                            <span className="px-2 py-1 bg-green-100 rounded">
                                                {availableSubUnits.find(
                                                    (su) =>
                                                        (su.id || su.value) ==
                                                        data.sub_unit_id
                                                )?.name ||
                                                    availableSubUnits.find(
                                                        (su) =>
                                                            (su.id ||
                                                                su.value) ==
                                                            data.sub_unit_id
                                                    )?.label}
                                            </span>
                                        </>
                                    )
                                ) : (
                                    <>
                                        <span>→</span>
                                        <span className="px-2 py-1 text-gray-600 bg-gray-100 rounded">
                                            Tidak ada sub unit
                                        </span>
                                    </>
                                )}
                            </div>
                        </div>
                    )}
                </div>

                {/* Main Work Information Section */}
                <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <InputField
                        name="nama_jabatan"
                        label="Nama Jabatan"
                        required={true}
                        placeholder="Contoh: Manager Operasional"
                        icon={Briefcase}
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
                        disabled={!data.tmt_mulai_kerja}
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
                        disabled={!data.tmt_mulai_jabatan}
                        hint="Harus diisi setelah TMT Mulai Jabatan diisi"
                    />
                </div>

                {/* Additional Work Information */}
                <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                    {/* NEW FIELD: Status Kerja - Read Only */}
                    <InputField
                        name="status_kerja"
                        label="Status Kerja"
                        value={data.status_kerja}
                        onChange={handleInputChange}
                        error={errors.status_kerja}
                        readonly={true}
                        hint="Otomatis berdasarkan TMT Berakhir Kerja"
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

                    {/* NEW FIELD: Masa Kerja - Read Only */}
                    <InputField
                        name="masa_kerja"
                        label="Masa Kerja"
                        value={data.masa_kerja}
                        onChange={handleInputChange}
                        readonly={true}
                        icon={Calendar}
                        hint="Otomatis dihitung berdasarkan TMT Mulai Kerja"
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
                        readonly={true}
                        hint={
                            calculatedAge
                                ? `Otomatis dihitung dari tanggal lahir (Usia saat ini: ${calculatedAge} tahun)`
                                : "Akan otomatis terisi saat tanggal lahir diisi"
                        }
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
                    icon={GraduationCap}
                    value={data.pendidikan_terakhir}
                    onChange={handleInputChange}
                    error={errors.pendidikan_terakhir}
                />
                <InputField
                    name="instansi_pendidikan"
                    label="Instansi Pendidikan"
                    placeholder="Nama sekolah/universitas"
                    icon={GraduationCap}
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
                    disabled={true}
                    error={errors.seragam}
                    hint="Field ini akan diisi oleh admin"
                />
                <InputField
                    name="jenis_sepatu"
                    label="Jenis Sepatu"
                    options={["Pantofel", "Safety Shoes"]}
                    icon={Briefcase}
                    value={data.jenis_sepatu}
                    onChange={handleInputChange}
                    error={errors.jenis_sepatu}
                    hint="Jenis sepatu kerja yang digunakan"
                />
                <InputField
                    name="ukuran_sepatu"
                    label="Ukuran Sepatu"
                    type="number"
                    placeholder="42"
                    icon={Briefcase}
                    value={data.ukuran_sepatu}
                    onChange={handleInputChange}
                    onBlur={handleInputBlur}
                    error={errors.ukuran_sepatu || formValidation.ukuran_sepatu}
                    hint="Ukuran sepatu (30-50)"
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
            <Head title="Tambah Karyawan" />

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
                                Tambah Karyawan Baru
                            </h1>
                            <p className="text-sm text-gray-600">
                                Lengkapi data karyawan dengan teliti. SEMUA
                                struktur organisasi (Unit Organisasi, Unit, Sub
                                Unit) WAJIB diisi.
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
                                    Simpan Karyawan
                                </>
                            )}
                        </button>
                    </div>
                </form>
            </div>
        </DashboardLayout>
    );
}
