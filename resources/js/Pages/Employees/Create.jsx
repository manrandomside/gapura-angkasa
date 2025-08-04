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
    hint = null,
}) => {
    const [focused, setFocused] = useState(false);
    const [hasValue, setHasValue] = useState(false);

    useEffect(() => {
        setHasValue(Boolean(value));
    }, [value]);

    const handleChange = (e) => {
        onChange(name, e.target.value);
        setHasValue(Boolean(e.target.value));
    };

    const handleBlur = (e) => {
        setFocused(false);
        if (onBlur) {
            onBlur(name, e.target.value);
        }
    };

    const handleFocus = () => {
        setFocused(true);
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
                    disabled={disabled}
                    className={`w-full px-4 py-3 text-gray-900 transition-all duration-300 border-2 rounded-xl focus:ring-4 focus:ring-[#439454]/20 focus:border-[#439454] hover:border-[#439454]/60 disabled:opacity-50 disabled:cursor-not-allowed ${
                        error
                            ? "border-red-300 bg-red-50"
                            : focused
                            ? "border-[#439454] bg-white"
                            : "border-gray-300 bg-white"
                    }`}
                >
                    <option value="">Pilih {label}</option>
                    {options.map((option) => (
                        <option key={option} value={option}>
                            {option}
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
                    rows={4}
                    className={`w-full px-4 py-3 text-gray-900 transition-all duration-300 border-2 rounded-xl focus:ring-4 focus:ring-[#439454]/20 focus:border-[#439454] hover:border-[#439454]/60 resize-none disabled:opacity-50 disabled:cursor-not-allowed ${
                        error
                            ? "border-red-300 bg-red-50"
                            : focused
                            ? "border-[#439454] bg-white"
                            : "border-gray-300 bg-white"
                    }`}
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
                    className={`w-full px-4 py-3 text-gray-900 transition-all duration-300 border-2 rounded-xl focus:ring-4 focus:ring-[#439454]/20 focus:border-[#439454] hover:border-[#439454]/60 disabled:opacity-50 disabled:cursor-not-allowed ${
                        error
                            ? "border-red-300 bg-red-50"
                            : focused
                            ? "border-[#439454] bg-white"
                            : "border-gray-300 bg-white"
                    }`}
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
    success = null,
    error = null,
    message = null,
}) {
    const { data, setData, post, processing, errors, reset, clearErrors } =
        useForm({
            nip: "",
            nama_lengkap: "",
            jenis_kelamin: "",
            tempat_lahir: "",
            tanggal_lahir: "",
            alamat: "",
            handphone: "",
            email: "",
            unit_organisasi: "",
            jabatan: "",
            nama_jabatan: "",
            status_pegawai: "PEGAWAI TETAP",
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
            kota_domisili: "",
            height: "",
            weight: "",
            no_bpjs_kesehatan: "",
            no_bpjs_ketenagakerjaan: "",
            seragam: "",
        });

    const [activeSection, setActiveSection] = useState("personal");
    const [formValidation, setFormValidation] = useState({});
    const [notification, setNotification] = useState(null);
    const [isSubmitting, setIsSubmitting] = useState(false);

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

    // Auto-calculate pension date when birth date changes
    useEffect(() => {
        if (data.tanggal_lahir) {
            const birthDate = new Date(data.tanggal_lahir);
            const pensionDate = new Date(birthDate);
            pensionDate.setFullYear(birthDate.getFullYear() + 60);
            setData("tmt_pensiun", pensionDate.toISOString().split("T")[0]);
        }
    }, [data.tanggal_lahir]);

    // Auto-sync jabatan fields
    useEffect(() => {
        if (data.nama_jabatan && !data.jabatan) {
            setData("jabatan", data.nama_jabatan);
        }
    }, [data.nama_jabatan]);

    const sections = {
        personal: { name: "Data Pribadi", icon: User },
        work: { name: "Data Pekerjaan", icon: Building2 },
        education: { name: "Pendidikan", icon: GraduationCap },
        additional: { name: "Data Tambahan", icon: FileText },
    };

    const validateField = (fieldName, value) => {
        let error = "";

        switch (fieldName) {
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
            case "nip":
                if (value && !/^[0-9]+$/.test(value)) {
                    error = "NIP hanya boleh berisi angka";
                } else if (value && value.length < 6) {
                    error = "NIP minimal 6 digit";
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

    const handleInputChange = (name, value) => {
        setData(name, value);

        // Real-time validation for important fields
        if (["nip", "email", "handphone"].includes(name)) {
            setTimeout(() => validateField(name, value), 500);
        }
    };

    const handleInputBlur = (name, value) => {
        validateField(name, value);
    };

    const validateRequiredFields = () => {
        const requiredFields = {
            nip: "NIP wajib diisi",
            nama_lengkap: "Nama lengkap wajib diisi",
            jenis_kelamin: "Jenis kelamin wajib dipilih",
            unit_organisasi: "Unit organisasi wajib dipilih",
            nama_jabatan: "Nama jabatan wajib diisi",
        };

        const newErrors = {};

        Object.entries(requiredFields).forEach(([field, message]) => {
            if (!data[field] || data[field].trim() === "") {
                newErrors[field] = message;
            }
        });

        return newErrors;
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        setIsSubmitting(true);

        // Validate required fields
        const requiredFieldErrors = validateRequiredFields();
        if (Object.keys(requiredFieldErrors).length > 0) {
            setFormValidation((prev) => ({ ...prev, ...requiredFieldErrors }));
            setNotification({
                type: "error",
                title: "Data Tidak Lengkap",
                message: "Mohon lengkapi semua field yang wajib diisi",
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

        post(route("employees.store"), {
            onSuccess: () => {
                setNotification({
                    type: "success",
                    title: "Berhasil!",
                    message:
                        "Karyawan berhasil ditambahkan. Mengalihkan ke daftar karyawan...",
                });

                // Redirect after 2 seconds untuk memberikan waktu user melihat notification
                setTimeout(() => {
                    router.visit(route("employees.index"));
                }, 2000);
            },
            onError: (errors) => {
                let errorMessage = "Terjadi kesalahan saat menyimpan data";

                if (errors.nip) {
                    errorMessage = "NIP sudah digunakan atau tidak valid";
                } else if (errors.email) {
                    errorMessage = "Email sudah digunakan atau tidak valid";
                } else if (Object.keys(errors).length > 0) {
                    errorMessage =
                        "Data yang diisi tidak valid. Silakan periksa kembali.";
                }

                setNotification({
                    type: "error",
                    title: "Gagal Menyimpan",
                    message: errorMessage,
                });
                setIsSubmitting(false);
            },
            onFinish: () => {
                setIsSubmitting(false);
            },
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

    const renderPersonalSection = () => (
        <div className="space-y-6">
            <h2 className="flex items-center gap-2 text-lg font-bold text-gray-900">
                <User className="w-5 h-5 text-[#439454]" />
                Data Pribadi
            </h2>

            <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                <InputField
                    name="nip"
                    label="NIP"
                    required={true}
                    placeholder="Contoh: 20241234"
                    icon={User}
                    value={data.nip}
                    onChange={handleInputChange}
                    onBlur={handleInputBlur}
                    error={errors.nip || formValidation.nip}
                    hint="Nomor Induk Pegawai (minimal 6 digit)"
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
                    hint="Tanggal pensiun akan otomatis dihitung (usia 60 tahun)"
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
        </div>
    );

    const renderWorkSection = () => (
        <div className="space-y-6">
            <h2 className="flex items-center gap-2 text-lg font-bold text-gray-900">
                <Building2 className="w-5 h-5 text-[#439454]" />
                Data Pekerjaan
            </h2>

            <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                <InputField
                    name="unit_organisasi"
                    label="Unit Organisasi"
                    required={true}
                    options={
                        unitOptions.length > 0
                            ? unitOptions
                            : [
                                  "Back Office",
                                  "Front Office",
                                  "Security",
                                  "Ground Handling",
                                  "Cargo",
                                  "Aviation Security",
                                  "Passenger Service",
                                  "Ramp",
                              ]
                    }
                    icon={Building2}
                    value={data.unit_organisasi}
                    onChange={handleInputChange}
                    error={
                        errors.unit_organisasi || formValidation.unit_organisasi
                    }
                />
                <InputField
                    name="nama_jabatan"
                    label="Nama Jabatan"
                    required={true}
                    placeholder="Contoh: Manager Operasional"
                    icon={UserCheck}
                    value={data.nama_jabatan}
                    onChange={handleInputChange}
                    error={errors.nama_jabatan || formValidation.nama_jabatan}
                />
                <InputField
                    name="status_pegawai"
                    label="Status Pegawai"
                    required={true}
                    options={[
                        "PEGAWAI TETAP",
                        "PEGAWAI KONTRAK",
                        "PEGAWAI MAGANG",
                    ]}
                    icon={UserCheck}
                    value={data.status_pegawai}
                    onChange={handleInputChange}
                    error={errors.status_pegawai}
                />
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
                    name="tmt_mulai_jabatan"
                    label="TMT Mulai Jabatan"
                    type="date"
                    icon={Calendar}
                    value={data.tmt_mulai_jabatan}
                    onChange={handleInputChange}
                    error={errors.tmt_mulai_jabatan}
                    hint="Tanggal Mulai Tugas pada jabatan saat ini"
                />
                <InputField
                    name="tmt_pensiun"
                    label="TMT Pensiun"
                    type="date"
                    icon={Calendar}
                    value={data.tmt_pensiun}
                    onChange={handleInputChange}
                    error={errors.tmt_pensiun}
                    hint="Otomatis terisi berdasarkan tanggal lahir"
                    disabled={!data.tanggal_lahir}
                />
            </div>
        </div>
    );

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
                    value=""
                    onChange={() => {}} // No-op function
                    disabled={true}
                    error={errors.seragam}
                    hint="Field ini akan diisi oleh admin"
                />
                <InputField
                    name="jenis_sepatu"
                    label="Jenis Sepatu"
                    options={["Pantofel", "Safety Shoes"]}
                    icon={Shield}
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
                    icon={Shield}
                    value={data.ukuran_sepatu}
                    onChange={handleInputChange}
                    error={errors.ukuran_sepatu}
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
                <div></div>
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
                                Lengkapi data karyawan dengan teliti
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
