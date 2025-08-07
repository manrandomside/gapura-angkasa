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

// Enhanced InputField component untuk edit dengan styling yang konsisten
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
            "w-full px-4 py-3 text-gray-900 transition-all duration-300 border-2 rounded-xl focus:ring-4 focus:ring-[#439454]/20";

        if (readonly) {
            return `${baseClasses} bg-gray-100 border-gray-300 cursor-not-allowed text-gray-600`;
        }

        if (disabled) {
            return `${baseClasses} border-gray-300 bg-gray-50 cursor-not-allowed opacity-50`;
        }

        if (error) {
            return `${baseClasses} border-red-300 bg-red-50`;
        }

        if (focused) {
            return `${baseClasses} border-[#439454] bg-white shadow-lg`;
        }

        return `${baseClasses} border-gray-300 bg-white hover:border-[#439454]/60`;
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
                    <option value="">Pilih {label}</option>
                    {options.map((option, index) => (
                        <option key={index} value={option}>
                            {option}
                        </option>
                    ))}
                </select>
            ) : (
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
            )}

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

// Notification component
const Notification = ({ type, title, message, onClose }) => {
    const [visible, setVisible] = useState(true);

    useEffect(() => {
        if (onClose) {
            const timer = setTimeout(() => {
                setVisible(false);
                setTimeout(onClose, 300);
            }, 5000);
            return () => clearTimeout(timer);
        }
    }, [onClose]);

    const getIcon = () => {
        switch (type) {
            case "success":
                return <CheckCircle className="w-5 h-5 text-green-600" />;
            case "error":
                return <AlertCircle className="w-5 h-5 text-red-600" />;
            case "warning":
                return <AlertTriangle className="w-5 h-5 text-yellow-600" />;
            default:
                return <Info className="w-5 h-5 text-blue-600" />;
        }
    };

    const getStyles = () => {
        switch (type) {
            case "success":
                return "bg-green-50 border-green-200 text-green-800";
            case "error":
                return "bg-red-50 border-red-200 text-red-800";
            case "warning":
                return "bg-yellow-50 border-yellow-200 text-yellow-800";
            default:
                return "bg-blue-50 border-blue-200 text-blue-800";
        }
    };

    return (
        <div
            className={`fixed top-4 right-4 z-50 max-w-md p-4 border-2 rounded-xl shadow-lg transform transition-all duration-300 ${getStyles()} ${
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

export default function Edit({
    employee,
    organizations = [],
    unitOptions = [],
    jabatanOptions = [],
    success = null,
    error = null,
    message = null,
}) {
    const { data, setData, put, processing, errors, reset, clearErrors } =
        useForm({
            nip: employee?.nip || "",
            nama_lengkap: employee?.nama_lengkap || "",
            jenis_kelamin: employee?.jenis_kelamin || "",
            tempat_lahir: employee?.tempat_lahir || "",
            tanggal_lahir: employee?.tanggal_lahir || "",
            alamat: employee?.alamat || "",
            handphone: employee?.handphone || "",
            email: employee?.email || "",
            unit_organisasi: employee?.unit_organisasi || "",
            jabatan: employee?.jabatan || "",
            nama_jabatan: employee?.nama_jabatan || "",
            status_pegawai: employee?.status_pegawai || "PEGAWAI TETAP",
            tmt_mulai_jabatan: employee?.tmt_mulai_jabatan || "",
            tmt_mulai_kerja: employee?.tmt_mulai_kerja || "",
            tmt_pensiun: employee?.tmt_pensiun || "",
            pendidikan_terakhir: employee?.pendidikan_terakhir || "",
            pendidikan: employee?.pendidikan || "",
            instansi_pendidikan: employee?.instansi_pendidikan || "",
            jurusan: employee?.jurusan || "",
            tahun_lulus: employee?.tahun_lulus || "",
            jenis_sepatu: employee?.jenis_sepatu || "",
            ukuran_sepatu: employee?.ukuran_sepatu || "",
            kota_domisili: employee?.kota_domisili || "",
            height: employee?.height || "",
            weight: employee?.weight || "",
            no_bpjs_kesehatan: employee?.no_bpjs_kesehatan || "",
            no_bpjs_ketenagakerjaan: employee?.no_bpjs_ketenagakerjaan || "",
            seragam: employee?.seragam || "",
            nik: employee?.nik || "",
            no_telepon: employee?.no_telepon || "",
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
        } else if (message) {
            setNotification({
                type: "info",
                title: "Informasi",
                message: message,
            });
        }
    }, [success, error, message]);

    // Form sections
    const sections = [
        { id: "personal", name: "Data Pribadi", icon: User },
        { id: "contact", name: "Kontak", icon: Phone },
        { id: "work", name: "Pekerjaan", icon: Building2 },
        { id: "education", name: "Pendidikan", icon: GraduationCap },
        { id: "additional", name: "Tambahan", icon: FileText },
    ];

    // Handle input changes
    const handleInputChange = (name, value) => {
        setData(name, value);
        clearErrors(name);

        // Clear validation error for this field
        if (formValidation[name]) {
            setFormValidation((prev) => ({
                ...prev,
                [name]: null,
            }));
        }
    };

    // Handle input blur for validation
    const handleInputBlur = (name, value) => {
        // Basic validation logic
        let validationError = null;

        switch (name) {
            case "nama_lengkap":
                if (!value || value.trim().length < 2) {
                    validationError = "Nama lengkap minimal 2 karakter";
                }
                break;
            case "email":
                if (value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                    validationError = "Format email tidak valid";
                }
                break;
            case "handphone":
                if (
                    value &&
                    !/^(\+62|62|0)[0-9]{9,13}$/.test(
                        value.replace(/[-\s]/g, "")
                    )
                ) {
                    validationError = "Format nomor handphone tidak valid";
                }
                break;
        }

        setFormValidation((prev) => ({
            ...prev,
            [name]: validationError,
        }));
    };

    // Handle form submission
    const handleSubmit = (e) => {
        e.preventDefault();
        setIsSubmitting(true);
        clearErrors();

        // Validate required fields
        const requiredFields = [
            "nama_lengkap",
            "jenis_kelamin",
            "unit_organisasi",
            "nama_jabatan",
            "status_pegawai",
        ];
        const missingFields = requiredFields.filter((field) => !data[field]);

        if (missingFields.length > 0) {
            setNotification({
                type: "error",
                title: "Form Tidak Lengkap",
                message: "Harap lengkapi semua field yang wajib diisi",
            });
            setIsSubmitting(false);
            return;
        }

        put(route("employees.update", employee.id), {
            onSuccess: () => {
                setNotification({
                    type: "success",
                    title: "Berhasil!",
                    message: "Data karyawan berhasil diperbarui",
                });
                setIsSubmitting(false);
            },
            onError: (errors) => {
                setNotification({
                    type: "error",
                    title: "Gagal Memperbarui!",
                    message: "Terjadi kesalahan saat memperbarui data karyawan",
                });
                setIsSubmitting(false);
            },
        });
    };

    // Handle cancel
    const handleCancel = () => {
        if (
            confirm("Batalkan perubahan? Data yang belum disimpan akan hilang.")
        ) {
            router.visit(route("employees.index"));
        }
    };

    // Render sections
    const renderSection = () => {
        switch (activeSection) {
            case "personal":
                return renderPersonalSection();
            case "contact":
                return renderContactSection();
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
                    icon={Shield}
                    value={data.nip}
                    onChange={handleInputChange}
                    onBlur={handleInputBlur}
                    error={errors.nip || formValidation.nip}
                    readonly={true}
                    hint="NIP tidak dapat diubah"
                />
                <InputField
                    name="nik"
                    label="NIK"
                    placeholder="3201234567890123"
                    icon={FileText}
                    value={data.nik}
                    onChange={handleInputChange}
                    onBlur={handleInputBlur}
                    error={errors.nik || formValidation.nik}
                />
                <div className="md:col-span-2">
                    <InputField
                        name="nama_lengkap"
                        label="Nama Lengkap"
                        required={true}
                        placeholder="Masukkan nama lengkap"
                        icon={User}
                        value={data.nama_lengkap}
                        onChange={handleInputChange}
                        onBlur={handleInputBlur}
                        error={
                            errors.nama_lengkap || formValidation.nama_lengkap
                        }
                    />
                </div>
                <InputField
                    name="jenis_kelamin"
                    label="Jenis Kelamin"
                    required={true}
                    options={["Laki-laki", "Perempuan"]}
                    icon={User}
                    value={data.jenis_kelamin}
                    onChange={handleInputChange}
                    onBlur={handleInputBlur}
                    error={errors.jenis_kelamin || formValidation.jenis_kelamin}
                />
                <InputField
                    name="tempat_lahir"
                    label="Tempat Lahir"
                    placeholder="Kota tempat lahir"
                    icon={MapPin}
                    value={data.tempat_lahir}
                    onChange={handleInputChange}
                    onBlur={handleInputBlur}
                    error={errors.tempat_lahir || formValidation.tempat_lahir}
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
                />
                <InputField
                    name="kota_domisili"
                    label="Kota Domisili"
                    placeholder="Kota tempat tinggal"
                    icon={MapPin}
                    value={data.kota_domisili}
                    onChange={handleInputChange}
                    onBlur={handleInputBlur}
                    error={errors.kota_domisili || formValidation.kota_domisili}
                />
                <div className="md:col-span-2">
                    <InputField
                        name="alamat"
                        label="Alamat Lengkap"
                        placeholder="Alamat lengkap tempat tinggal"
                        icon={MapPin}
                        value={data.alamat}
                        onChange={handleInputChange}
                        onBlur={handleInputBlur}
                        error={errors.alamat || formValidation.alamat}
                    />
                </div>
            </div>
        </div>
    );

    const renderContactSection = () => (
        <div className="space-y-6">
            <h2 className="flex items-center gap-2 text-lg font-bold text-gray-900">
                <Phone className="w-5 h-5 text-[#439454]" />
                Informasi Kontak
            </h2>

            <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                <InputField
                    name="handphone"
                    label="Nomor Handphone"
                    placeholder="0812-3456-7890"
                    icon={Phone}
                    value={data.handphone}
                    onChange={handleInputChange}
                    onBlur={handleInputBlur}
                    error={errors.handphone || formValidation.handphone}
                />
                <InputField
                    name="no_telepon"
                    label="No. Telepon"
                    placeholder="0361-123456"
                    icon={Phone}
                    value={data.no_telepon}
                    onChange={handleInputChange}
                    onBlur={handleInputBlur}
                    error={errors.no_telepon || formValidation.no_telepon}
                    hint="Nomor telepon rumah (opsional)"
                />
                <div className="md:col-span-2">
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
                                  "DIREKTORAT UTAMA",
                                  "DIREKTORAT OPERASI",
                                  "DIREKTORAT KEUANGAN",
                                  "DIVISI SDM",
                                  "DIVISI OPERASIONAL",
                                  "DIVISI KEUANGAN",
                                  "DIVISI TEKNOLOGI INFORMASI",
                                  "UNIT KEAMANAN PENERBANGAN",
                                  "UNIT PELAYANAN TEKNIK",
                                  "UNIT PELAYANAN OPERASI",
                              ]
                    }
                    icon={Building2}
                    value={data.unit_organisasi}
                    onChange={handleInputChange}
                    onBlur={handleInputBlur}
                    error={
                        errors.unit_organisasi || formValidation.unit_organisasi
                    }
                />
                <InputField
                    name="jabatan"
                    label="Jabatan"
                    placeholder="Jabatan (opsional)"
                    icon={UserCheck}
                    value={data.jabatan}
                    onChange={handleInputChange}
                    onBlur={handleInputBlur}
                    error={errors.jabatan || formValidation.jabatan}
                />
                <InputField
                    name="nama_jabatan"
                    label="Nama Jabatan"
                    required={true}
                    options={
                        jabatanOptions.length > 0
                            ? jabatanOptions
                            : [
                                  "Manager",
                                  "Assistant Manager",
                                  "Supervisor",
                                  "Staff",
                                  "Senior Staff",
                                  "Junior Staff",
                                  "Operator",
                                  "Technician",
                                  "Security",
                                  "Driver",
                              ]
                    }
                    icon={UserCheck}
                    value={data.nama_jabatan}
                    onChange={handleInputChange}
                    onBlur={handleInputBlur}
                    error={errors.nama_jabatan || formValidation.nama_jabatan}
                />
                <InputField
                    name="status_pegawai"
                    label="Status Pegawai"
                    required={true}
                    options={[
                        "PEGAWAI TETAP",
                        "TAD",
                        "PEGAWAI KONTRAK",
                        "PEGAWAI MAGANG",
                    ]}
                    icon={UserCheck}
                    value={data.status_pegawai}
                    onChange={handleInputChange}
                    onBlur={handleInputBlur}
                    error={
                        errors.status_pegawai || formValidation.status_pegawai
                    }
                />
                <InputField
                    name="tmt_mulai_jabatan"
                    label="TMT Mulai Jabatan"
                    type="date"
                    icon={Calendar}
                    value={data.tmt_mulai_jabatan}
                    onChange={handleInputChange}
                    onBlur={handleInputBlur}
                    error={
                        errors.tmt_mulai_jabatan ||
                        formValidation.tmt_mulai_jabatan
                    }
                />
                <InputField
                    name="tmt_mulai_kerja"
                    label="TMT Mulai Kerja"
                    type="date"
                    icon={Calendar}
                    value={data.tmt_mulai_kerja}
                    onChange={handleInputChange}
                    onBlur={handleInputBlur}
                    error={
                        errors.tmt_mulai_kerja || formValidation.tmt_mulai_kerja
                    }
                />
                <InputField
                    name="tmt_pensiun"
                    label="TMT Pensiun"
                    type="date"
                    icon={Calendar}
                    value={data.tmt_pensiun}
                    onChange={handleInputChange}
                    onBlur={handleInputBlur}
                    error={errors.tmt_pensiun || formValidation.tmt_pensiun}
                />
            </div>
        </div>
    );

    const renderEducationSection = () => (
        <div className="space-y-6">
            <h2 className="flex items-center gap-2 text-lg font-bold text-gray-900">
                <GraduationCap className="w-5 h-5 text-[#439454]" />
                Riwayat Pendidikan
            </h2>

            <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                <InputField
                    name="pendidikan_terakhir"
                    label="Pendidikan Terakhir"
                    options={[
                        "SD",
                        "SMP",
                        "SMA/SMK",
                        "D1",
                        "D2",
                        "D3",
                        "D4",
                        "S1",
                        "S2",
                        "S3",
                    ]}
                    icon={GraduationCap}
                    value={data.pendidikan_terakhir}
                    onChange={handleInputChange}
                    onBlur={handleInputBlur}
                    error={
                        errors.pendidikan_terakhir ||
                        formValidation.pendidikan_terakhir
                    }
                />
                <InputField
                    name="pendidikan"
                    label="Pendidikan"
                    placeholder="Detail pendidikan"
                    icon={GraduationCap}
                    value={data.pendidikan}
                    onChange={handleInputChange}
                    onBlur={handleInputBlur}
                    error={errors.pendidikan || formValidation.pendidikan}
                />
                <InputField
                    name="instansi_pendidikan"
                    label="Instansi Pendidikan"
                    placeholder="Nama sekolah/universitas"
                    icon={Building2}
                    value={data.instansi_pendidikan}
                    onChange={handleInputChange}
                    onBlur={handleInputBlur}
                    error={
                        errors.instansi_pendidikan ||
                        formValidation.instansi_pendidikan
                    }
                />
                <InputField
                    name="jurusan"
                    label="Jurusan/Program Studi"
                    placeholder="Nama jurusan"
                    icon={GraduationCap}
                    value={data.jurusan}
                    onChange={handleInputChange}
                    onBlur={handleInputBlur}
                    error={errors.jurusan || formValidation.jurusan}
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

            <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                <InputField
                    name="jenis_sepatu"
                    label="Jenis Sepatu"
                    options={["Pantofel", "Safety Shoes"]}
                    icon={Shield}
                    value={data.jenis_sepatu}
                    onChange={handleInputChange}
                    onBlur={handleInputBlur}
                    error={errors.jenis_sepatu || formValidation.jenis_sepatu}
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
                    ]}
                    icon={Shield}
                    value={data.ukuran_sepatu}
                    onChange={handleInputChange}
                    onBlur={handleInputBlur}
                    error={errors.ukuran_sepatu || formValidation.ukuran_sepatu}
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
                />
                <InputField
                    name="weight"
                    label="Berat Badan (kg)"
                    type="number"
                    placeholder="65"
                    icon={User}
                    value={data.weight}
                    onChange={handleInputChange}
                    onBlur={handleInputBlur}
                    error={errors.weight || formValidation.weight}
                />
                <InputField
                    name="no_bpjs_kesehatan"
                    label="No. BPJS Kesehatan"
                    placeholder="0001234567890"
                    icon={Shield}
                    value={data.no_bpjs_kesehatan}
                    onChange={handleInputChange}
                    onBlur={handleInputBlur}
                    error={
                        errors.no_bpjs_kesehatan ||
                        formValidation.no_bpjs_kesehatan
                    }
                />
                <InputField
                    name="no_bpjs_ketenagakerjaan"
                    label="No. BPJS Ketenagakerjaan"
                    placeholder="0001234567890"
                    icon={Shield}
                    value={data.no_bpjs_ketenagakerjaan}
                    onChange={handleInputChange}
                    onBlur={handleInputBlur}
                    error={
                        errors.no_bpjs_ketenagakerjaan ||
                        formValidation.no_bpjs_ketenagakerjaan
                    }
                />
                <InputField
                    name="seragam"
                    label="Ukuran Seragam"
                    options={["XS", "S", "M", "L", "XL", "XXL", "XXXL"]}
                    icon={User}
                    value={data.seragam}
                    onChange={handleInputChange}
                    onBlur={handleInputBlur}
                    error={errors.seragam || formValidation.seragam}
                />
            </div>
        </div>
    );

    return (
        <DashboardLayout>
            <Head
                title={`Edit Karyawan - ${employee?.nama_lengkap || "Unknown"}`}
            />

            {/* Notification */}
            {notification && (
                <Notification
                    type={notification.type}
                    title={notification.title}
                    message={notification.message}
                    onClose={() => setNotification(null)}
                />
            )}

            <div className="p-6 space-y-6">
                {/* Header */}
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div className="flex items-center gap-2 mb-2">
                            <button
                                onClick={() =>
                                    router.visit(route("employees.index"))
                                }
                                className="p-2 text-gray-600 transition-all duration-300 rounded-xl hover:text-[#439454] hover:bg-white"
                            >
                                <ArrowLeft className="w-5 h-5" />
                            </button>
                            <h1 className="text-2xl font-bold text-gray-900">
                                Edit Karyawan
                            </h1>
                        </div>
                        <p className="text-gray-600">
                            Perbarui data karyawan{" "}
                            {employee?.nama_lengkap || "Unknown"}
                        </p>
                    </div>
                </div>

                {/* Section Navigation */}
                <div className="flex flex-wrap gap-2 p-2 bg-gray-100 rounded-xl">
                    {sections.map((section) => (
                        <button
                            key={section.id}
                            onClick={() => setActiveSection(section.id)}
                            className={`flex items-center gap-2 px-4 py-2 text-sm font-medium transition-all duration-300 rounded-xl ${
                                activeSection === section.id
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
