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
} from "lucide-react";

// Komponen InputField yang sudah diperbaiki
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
}) => {
    const handleChange = (e) => {
        onChange(name, e.target.value);
    };

    const handleBlur = (e) => {
        if (onBlur) {
            onBlur(name, e.target.value);
        }
    };

    return (
        <div className="space-y-2">
            <label className="flex items-center gap-2 text-sm font-semibold text-gray-700">
                {Icon && <Icon className="w-4 h-4 text-[#439454]" />}
                {label}
                {required && <span className="text-red-500">*</span>}
            </label>

            {options ? (
                <select
                    name={name}
                    value={value || ""}
                    onChange={handleChange}
                    onBlur={handleBlur}
                    className={`w-full px-4 py-3 text-gray-900 transition-all duration-300 border-2 rounded-xl focus:ring-4 focus:ring-[#439454]/20 focus:border-[#439454] hover:border-[#439454]/60 ${
                        error
                            ? "border-red-300 bg-red-50"
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
                    placeholder={placeholder}
                    rows={4}
                    className={`w-full px-4 py-3 text-gray-900 transition-all duration-300 border-2 rounded-xl focus:ring-4 focus:ring-[#439454]/20 focus:border-[#439454] hover:border-[#439454]/60 resize-none ${
                        error
                            ? "border-red-300 bg-red-50"
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
                    placeholder={placeholder}
                    className={`w-full px-4 py-3 text-gray-900 transition-all duration-300 border-2 rounded-xl focus:ring-4 focus:ring-[#439454]/20 focus:border-[#439454] hover:border-[#439454]/60 ${
                        error
                            ? "border-red-300 bg-red-50"
                            : "border-gray-300 bg-white"
                    }`}
                />
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

    // Auto-calculate pension date when birth date changes
    useEffect(() => {
        if (data.tanggal_lahir) {
            const birthDate = new Date(data.tanggal_lahir);
            const pensionDate = new Date(birthDate);
            pensionDate.setFullYear(birthDate.getFullYear() + 60);
            setData("tmt_pensiun", pensionDate.toISOString().split("T")[0]);
        }
    }, [data.tanggal_lahir]);

    const sections = {
        personal: { name: "Data Pribadi", icon: User },
        work: { name: "Data Pekerjaan", icon: Building2 },
        education: { name: "Pendidikan", icon: GraduationCap },
        additional: { name: "Data Tambahan", icon: FileText },
    };

    const validateField = (fieldName, value) => {
        let error = "";

        if (fieldName === "email" && value) {
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                error = "Format email tidak valid";
            }
        }

        if (fieldName === "handphone" && value) {
            if (!/^[0-9+\-\s()]+$/.test(value)) {
                error = "Format nomor handphone tidak valid";
            }
        }

        if (fieldName === "nip" && value) {
            if (!/^[0-9]+$/.test(value)) {
                error = "NIP hanya boleh berisi angka";
            }
        }

        setFormValidation((prev) => ({
            ...prev,
            [fieldName]: error,
        }));
    };

    const handleInputChange = (name, value) => {
        setData(name, value);
    };

    const handleInputBlur = (name, value) => {
        validateField(name, value);
    };

    const handleSubmit = (e) => {
        e.preventDefault();

        post("/employees", {
            onSuccess: () => {
                setShowSuccess(true);
                setTimeout(() => {
                    router.visit("/employees");
                }, 2000);
            },
            onError: () => {
                // Error handling
            },
        });
    };

    const handleCancel = () => {
        if (
            confirm(
                "Yakin ingin membatalkan? Data yang sudah diisi akan hilang."
            )
        ) {
            router.visit("/employees");
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
                />
                <InputField
                    name="nama_lengkap"
                    label="Nama Lengkap"
                    required={true}
                    placeholder="Nama lengkap karyawan"
                    icon={User}
                    value={data.nama_lengkap}
                    onChange={handleInputChange}
                    error={errors.nama_lengkap}
                />
                <InputField
                    name="jenis_kelamin"
                    label="Jenis Kelamin"
                    required={true}
                    options={["Laki-laki", "Perempuan"]}
                    icon={User}
                    value={data.jenis_kelamin}
                    onChange={handleInputChange}
                    error={errors.jenis_kelamin}
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
                    error={errors.tanggal_lahir}
                />
                <InputField
                    name="kota_domisili"
                    label="Kota Domisili"
                    placeholder="Kota tempat tinggal"
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
                    name="no_telepon"
                    label="No. Telepon"
                    placeholder="021-1234567"
                    icon={Phone}
                    value={data.no_telepon}
                    onChange={handleInputChange}
                    error={errors.no_telepon}
                />
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
                    value={data.unit_organisasi}
                    onChange={handleInputChange}
                    error={errors.unit_organisasi}
                />
                <InputField
                    name="nama_jabatan"
                    label="Nama Jabatan"
                    required={true}
                    placeholder="Contoh: Manager Operasional"
                    icon={UserCheck}
                    value={data.nama_jabatan}
                    onChange={handleInputChange}
                    error={errors.nama_jabatan}
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
                />
                <InputField
                    name="tmt_mulai_jabatan"
                    label="TMT Mulai Jabatan"
                    type="date"
                    icon={Calendar}
                    value={data.tmt_mulai_jabatan}
                    onChange={handleInputChange}
                    error={errors.tmt_mulai_jabatan}
                />
                <InputField
                    name="tmt_pensiun"
                    label="TMT Pensiun"
                    type="date"
                    icon={Calendar}
                    value={data.tmt_pensiun}
                    onChange={handleInputChange}
                    error={errors.tmt_pensiun}
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
                    placeholder="Nama jurusan"
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
                    error={errors.tahun_lulus}
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
                    options={[
                        "Safety Boots",
                        "Formal Shoes",
                        "Sneakers",
                        "Sandals",
                    ]}
                    icon={Shield}
                    value={data.jenis_sepatu}
                    onChange={handleInputChange}
                    error={errors.jenis_sepatu}
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
                    error={errors.height}
                />
                <InputField
                    name="weight"
                    label="Berat Badan (kg)"
                    type="number"
                    placeholder="70"
                    icon={User}
                    value={data.weight}
                    onChange={handleInputChange}
                    error={errors.weight}
                />
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

            {/* Success Notification */}
            {showSuccess && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                    <div className="p-8 bg-white border-2 border-green-300 shadow-2xl rounded-2xl">
                        <div className="flex items-center gap-4">
                            <div className="flex items-center justify-center w-12 h-12 bg-green-100 rounded-full">
                                <CheckCircle className="w-6 h-6 text-green-600" />
                            </div>
                            <div>
                                <h3 className="text-lg font-bold text-gray-900">
                                    Berhasil!
                                </h3>
                                <p className="text-sm text-gray-600">
                                    Karyawan baru berhasil ditambahkan
                                </p>
                            </div>
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
                    <div className="p-6 space-y-6">{renderSection()}</div>

                    {/* Form Actions */}
                    <div className="flex items-center justify-end gap-4 p-6 border-t border-gray-200 bg-gray-50 rounded-b-2xl">
                        <button
                            type="button"
                            onClick={handleCancel}
                            className="flex items-center gap-2 px-6 py-3 text-gray-600 transition-all duration-300 border-2 border-gray-300 rounded-xl hover:border-red-400 hover:text-red-600 focus:ring-4 focus:ring-red-400/20"
                        >
                            <X className="w-4 h-4" />
                            Batal
                        </button>
                        <button
                            type="submit"
                            disabled={processing}
                            className="flex items-center gap-2 px-6 py-3 text-white transition-all duration-300 bg-[#439454] border-2 border-[#439454] rounded-xl hover:bg-[#439454]/90 focus:ring-4 focus:ring-[#439454]/20 disabled:opacity-50 disabled:cursor-not-allowed"
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
                    </div>
                </form>
            </div>
        </DashboardLayout>
    );
}
