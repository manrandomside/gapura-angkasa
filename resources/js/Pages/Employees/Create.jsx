import React, { useState, useEffect, useCallback, useMemo } from "react";
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
} from "lucide-react";

export default function Create({
    organizations = [],
    unitOptions = [],
    jabatanOptions = [],
}) {
    const { data, setData, post, processing, errors, reset } = useForm({
        nip: "",
        nama_lengkap: "",
        jenis_kelamin: "",
        tempat_lahir: "",
        tanggal_lahir: "",
        alamat: "",
        no_telepon: "",
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
    });

    const [activeSection, setActiveSection] = useState("personal");
    const [formValidation, setFormValidation] = useState({});
    const [showSuccess, setShowSuccess] = useState(false);

    // Calculate age when birth date changes
    useEffect(() => {
        if (data.tanggal_lahir) {
            const birthDate = new Date(data.tanggal_lahir);
            const today = new Date();
            const age = today.getFullYear() - birthDate.getFullYear();
            const monthDiff = today.getMonth() - birthDate.getMonth();

            if (
                monthDiff < 0 ||
                (monthDiff === 0 && today.getDate() < birthDate.getDate())
            ) {
                age--;
            }

            // Auto-calculate pension date (assuming 60 years old)
            const pensionDate = new Date(birthDate);
            pensionDate.setFullYear(birthDate.getFullYear() + 60);
            setData("tmt_pensiun", pensionDate.toISOString().split("T")[0]);
        }
    }, [data.tanggal_lahir]);

    const sections = {
        personal: { name: "Data Pribadi", icon: User },
        work: { name: "Data Pekerjaan", icon: Building2 },
        education: { name: "Pendidikan", icon: GraduationCap },
        additional: { name: "Data Tambahan", icon: UserCheck },
    };

    const handleSubmit = (e) => {
        e.preventDefault();

        post(route("employees.store"), {
            onSuccess: () => {
                setShowSuccess(true);
                setTimeout(() => {
                    router.visit(route("employees.index"));
                }, 1500);
            },
            onError: (errors) => {
                setFormValidation(errors);
                // Scroll to first error
                const firstErrorField = Object.keys(errors)[0];
                const errorElement = document.querySelector(
                    `[name="${firstErrorField}"]`
                );
                if (errorElement) {
                    errorElement.scrollIntoView({
                        behavior: "smooth",
                        block: "center",
                    });
                    errorElement.focus();
                }
            },
        });
    };

    const handleCancel = () => {
        if (
            confirm(
                "Apakah Anda yakin ingin membatalkan? Data yang sudah diisi akan hilang."
            )
        ) {
            router.visit(route("employees.index"));
        }
    };

    const validateField = (fieldName, value) => {
        let error = null;

        switch (fieldName) {
            case "nip":
                if (!value) error = "NIP harus diisi";
                else if (value.length < 4) error = "NIP minimal 4 karakter";
                break;
            case "nama_lengkap":
                if (!value) error = "Nama lengkap harus diisi";
                else if (value.length < 3) error = "Nama minimal 3 karakter";
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
        }

        setFormValidation((prev) => ({
            ...prev,
            [fieldName]: error,
        }));
    };

    // Stable event handlers using useCallback
    const handleInputChange = useCallback(
        (name, value) => {
            setData(name, value);
        },
        [setData]
    );

    const handleInputBlur = useCallback((name, value) => {
        validateField(name, value);
    }, []);

    const InputField = useMemo(
        () =>
            ({
                name,
                label,
                type = "text",
                required = false,
                options = null,
                className = "",
                placeholder = "",
                icon: Icon = null,
            }) => {
                const hasError = errors[name] || formValidation[name];

                return (
                    <div className={`space-y-2 ${className}`} key={name}>
                        <label className="flex items-center gap-2 text-sm font-semibold text-gray-700">
                            {Icon && (
                                <Icon className="w-4 h-4 text-[#439454]" />
                            )}
                            {label}
                            {required && (
                                <span className="text-red-500">*</span>
                            )}
                        </label>

                        {options ? (
                            <select
                                name={name}
                                value={data[name] || ""}
                                onChange={(e) =>
                                    handleInputChange(name, e.target.value)
                                }
                                onBlur={(e) =>
                                    handleInputBlur(name, e.target.value)
                                }
                                className={`w-full px-4 py-3 text-gray-900 transition-all duration-300 border-2 rounded-xl focus:ring-4 focus:ring-[#439454]/20 focus:border-[#439454] hover:border-[#439454]/60 ${
                                    hasError
                                        ? "border-red-300 focus:border-red-500 focus:ring-red-500/20"
                                        : "border-gray-300"
                                }`}
                                required={required}
                            >
                                <option value="">
                                    {placeholder || `Pilih ${label}`}
                                </option>
                                {options.map((option, index) => (
                                    <option
                                        key={`${name}-${index}`}
                                        value={option}
                                    >
                                        {option}
                                    </option>
                                ))}
                            </select>
                        ) : (
                            <input
                                type={type}
                                name={name}
                                value={data[name] || ""}
                                onChange={(e) =>
                                    handleInputChange(name, e.target.value)
                                }
                                onBlur={(e) =>
                                    handleInputBlur(name, e.target.value)
                                }
                                placeholder={
                                    placeholder ||
                                    `Masukkan ${label.toLowerCase()}`
                                }
                                className={`w-full px-4 py-3 text-gray-900 transition-all duration-300 border-2 rounded-xl focus:ring-4 focus:ring-[#439454]/20 focus:border-[#439454] hover:border-[#439454]/60 ${
                                    hasError
                                        ? "border-red-300 focus:border-red-500 focus:ring-red-500/20"
                                        : "border-gray-300"
                                }`}
                                required={required}
                                autoComplete="off"
                            />
                        )}

                        {hasError && (
                            <div className="flex items-center gap-2 text-sm text-red-600">
                                <AlertCircle className="w-4 h-4" />
                                {errors[name] || formValidation[name]}
                            </div>
                        )}
                    </div>
                );
            },
        [data, errors, formValidation, handleInputChange, handleInputBlur]
    );

    return (
        <DashboardLayout>
            <Head title="Tambah Karyawan - GAPURA ANGKASA" />

            {/* Success Animation */}
            {showSuccess && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                    <div className="flex flex-col items-center gap-4 px-8 py-6 bg-white shadow-2xl rounded-2xl">
                        <div className="flex items-center justify-center w-16 h-16 bg-green-100 rounded-full">
                            <CheckCircle className="w-8 h-8 text-[#439454]" />
                        </div>
                        <div className="text-center">
                            <h3 className="text-lg font-bold text-gray-900">
                                Berhasil!
                            </h3>
                            <p className="text-sm text-gray-600">
                                Karyawan baru berhasil ditambahkan
                            </p>
                        </div>
                    </div>
                </div>
            )}

            <div className="p-6 space-y-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <button
                            onClick={handleCancel}
                            className="flex items-center gap-2 px-4 py-2 text-gray-600 transition-all duration-300 border-2 border-gray-300 rounded-xl hover:border-[#439454] hover:text-[#439454] focus:ring-4 focus:ring-[#439454]/20"
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
                <div className="flex gap-2 p-2 bg-gray-100 rounded-xl">
                    {Object.entries(sections).map(([key, section]) => (
                        <button
                            key={key}
                            onClick={() => setActiveSection(key)}
                            className={`flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg transition-all duration-300 ${
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
                    <div className="p-6 space-y-6">
                        {/* Personal Data Section */}
                        {activeSection === "personal" && (
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
                                    />
                                    <InputField
                                        name="nama_lengkap"
                                        label="Nama Lengkap"
                                        required={true}
                                        placeholder="Nama lengkap karyawan"
                                        icon={User}
                                    />
                                    <InputField
                                        name="jenis_kelamin"
                                        label="Jenis Kelamin"
                                        required={true}
                                        options={["Laki-laki", "Perempuan"]}
                                    />
                                    <InputField
                                        name="tempat_lahir"
                                        label="Tempat Lahir"
                                        placeholder="Kota tempat lahir"
                                        icon={MapPin}
                                    />
                                    <InputField
                                        name="tanggal_lahir"
                                        label="Tanggal Lahir"
                                        type="date"
                                        icon={Calendar}
                                    />
                                    <InputField
                                        name="kota_domisili"
                                        label="Kota Domisili"
                                        placeholder="Kota tempat tinggal saat ini"
                                        icon={MapPin}
                                    />
                                </div>

                                <InputField
                                    name="alamat"
                                    label="Alamat Lengkap"
                                    placeholder="Alamat lengkap tempat tinggal"
                                    icon={MapPin}
                                />

                                <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                                    <InputField
                                        name="no_telepon"
                                        label="Nomor Telepon"
                                        placeholder="Contoh: 021-1234567"
                                        icon={Phone}
                                    />
                                    <InputField
                                        name="handphone"
                                        label="Nomor Handphone"
                                        placeholder="Contoh: 081234567890"
                                        icon={Phone}
                                    />
                                    <InputField
                                        name="email"
                                        label="Email"
                                        type="email"
                                        placeholder="nama@email.com"
                                        icon={Mail}
                                    />
                                </div>
                            </div>
                        )}

                        {/* Work Data Section */}
                        {activeSection === "work" && (
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
                                        options={[
                                            "Back Office",
                                            "Front Office",
                                            "Security",
                                            "Ground Handling",
                                            "Cargo",
                                            "Aviation Security",
                                            "Passenger Service",
                                            "Ramp",
                                        ]}
                                        icon={Building2}
                                    />
                                    <InputField
                                        name="nama_jabatan"
                                        label="Nama Jabatan"
                                        required={true}
                                        placeholder="Contoh: Staff Admin"
                                        icon={UserCheck}
                                    />
                                    <InputField
                                        name="status_pegawai"
                                        label="Status Pegawai"
                                        required={true}
                                        options={["PEGAWAI TETAP", "TAD"]}
                                    />
                                    <InputField
                                        name="tmt_mulai_kerja"
                                        label="TMT Mulai Kerja"
                                        type="date"
                                        icon={Calendar}
                                    />
                                    <InputField
                                        name="tmt_mulai_jabatan"
                                        label="TMT Mulai Jabatan"
                                        type="date"
                                        icon={Calendar}
                                    />
                                    <InputField
                                        name="tmt_pensiun"
                                        label="TMT Pensiun"
                                        type="date"
                                        icon={Calendar}
                                    />
                                </div>
                            </div>
                        )}

                        {/* Education Section */}
                        {activeSection === "education" && (
                            <div className="space-y-6">
                                <h2 className="flex items-center gap-2 text-lg font-bold text-gray-900">
                                    <GraduationCap className="w-5 h-5 text-[#439454]" />
                                    Data Pendidikan
                                </h2>

                                <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                                    <InputField
                                        name="pendidikan_terakhir"
                                        label="Pendidikan Terakhir"
                                        options={[
                                            "SD",
                                            "SMP",
                                            "SMA",
                                            "D1",
                                            "D2",
                                            "D3",
                                            "S1",
                                            "S2",
                                            "S3",
                                        ]}
                                        icon={GraduationCap}
                                    />
                                    <InputField
                                        name="instansi_pendidikan"
                                        label="Instansi Pendidikan"
                                        placeholder="Nama sekolah/universitas"
                                        icon={GraduationCap}
                                    />
                                    <InputField
                                        name="jurusan"
                                        label="Jurusan"
                                        placeholder="Program studi/jurusan"
                                        icon={GraduationCap}
                                    />
                                    <InputField
                                        name="tahun_lulus"
                                        label="Tahun Lulus"
                                        type="number"
                                        placeholder="Contoh: 2020"
                                        icon={Calendar}
                                    />
                                </div>
                            </div>
                        )}

                        {/* Additional Data Section */}
                        {activeSection === "additional" && (
                            <div className="space-y-6">
                                <h2 className="flex items-center gap-2 text-lg font-bold text-gray-900">
                                    <UserCheck className="w-5 h-5 text-[#439454]" />
                                    Data Tambahan
                                </h2>

                                <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                                    <InputField
                                        name="jenis_sepatu"
                                        label="Jenis Sepatu"
                                        options={["Pantofel", "Safety Shoes"]}
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
                                        ]}
                                    />
                                    <InputField
                                        name="height"
                                        label="Tinggi Badan (cm)"
                                        type="number"
                                        placeholder="Contoh: 170"
                                    />
                                    <InputField
                                        name="weight"
                                        label="Berat Badan (kg)"
                                        type="number"
                                        placeholder="Contoh: 70"
                                    />
                                    <InputField
                                        name="no_bpjs_kesehatan"
                                        label="No. BPJS Kesehatan"
                                        placeholder="Nomor BPJS Kesehatan"
                                    />
                                    <InputField
                                        name="no_bpjs_ketenagakerjaan"
                                        label="No. BPJS Ketenagakerjaan"
                                        placeholder="Nomor BPJS Ketenagakerjaan"
                                    />
                                </div>
                            </div>
                        )}
                    </div>

                    {/* Form Actions */}
                    <div className="flex justify-between px-6 py-4 border-t border-gray-200 bg-gray-50/50">
                        <button
                            type="button"
                            onClick={handleCancel}
                            className="flex items-center gap-2 px-6 py-3 text-gray-700 transition-all duration-300 border-2 border-gray-300 rounded-xl hover:border-red-400 hover:text-red-600 focus:ring-4 focus:ring-red-500/20"
                        >
                            <X className="w-4 h-4" />
                            Batal
                        </button>

                        <div className="flex gap-3">
                            {activeSection !== "personal" && (
                                <button
                                    type="button"
                                    onClick={() => {
                                        const sections = [
                                            "personal",
                                            "work",
                                            "education",
                                            "additional",
                                        ];
                                        const currentIndex =
                                            sections.indexOf(activeSection);
                                        if (currentIndex > 0) {
                                            setActiveSection(
                                                sections[currentIndex - 1]
                                            );
                                        }
                                    }}
                                    className="px-6 py-3 text-[#439454] transition-all duration-300 border-2 border-[#439454] rounded-xl hover:bg-[#439454] hover:text-white focus:ring-4 focus:ring-[#439454]/20"
                                >
                                    Sebelumnya
                                </button>
                            )}

                            {activeSection !== "additional" ? (
                                <button
                                    type="button"
                                    onClick={() => {
                                        const sections = [
                                            "personal",
                                            "work",
                                            "education",
                                            "additional",
                                        ];
                                        const currentIndex =
                                            sections.indexOf(activeSection);
                                        if (
                                            currentIndex <
                                            sections.length - 1
                                        ) {
                                            setActiveSection(
                                                sections[currentIndex + 1]
                                            );
                                        }
                                    }}
                                    className="px-6 py-3 text-white transition-all duration-300 bg-[#439454] rounded-xl hover:bg-[#367a41] focus:ring-4 focus:ring-[#439454]/20 shadow-lg hover:shadow-xl"
                                >
                                    Selanjutnya
                                </button>
                            ) : (
                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="flex items-center gap-2 px-6 py-3 text-white transition-all duration-300 bg-gradient-to-r from-[#439454] to-[#367a41] rounded-xl hover:from-[#367a41] hover:to-[#2d6435] focus:ring-4 focus:ring-[#439454]/20 shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    {processing ? (
                                        <>
                                            <div className="w-4 h-4 border-2 border-white rounded-full border-t-transparent animate-spin"></div>
                                            Menyimpan...
                                        </>
                                    ) : (
                                        <>
                                            <Save className="w-4 h-4" />
                                            Simpan Karyawan
                                        </>
                                    )}
                                </button>
                            )}
                        </div>
                    </div>
                </form>
            </div>
        </DashboardLayout>
    );
}
