import React from "react";
import {
    X,
    User,
    Building,
    GraduationCap,
    Phone,
    MapPin,
    Clock,
    Shield,
    Briefcase,
    Calendar,
    Heart,
    Mail,
    CreditCard,
    Ruler,
    Weight,
    Baby,
    Shirt,
} from "lucide-react";

const EmployeeDetailModal = ({ employee, isOpen, onClose }) => {
    if (!isOpen || !employee) return null;

    const formatDate = (dateString) => {
        if (!dateString) return "-";
        try {
            return new Date(dateString).toLocaleDateString("id-ID", {
                day: "2-digit",
                month: "long",
                year: "numeric",
            });
        } catch {
            return "-";
        }
    };

    const calculateAge = (birthDate) => {
        if (!birthDate) return "-";
        const today = new Date();
        const birth = new Date(birthDate);
        const age = today.getFullYear() - birth.getFullYear();
        const monthDiff = today.getMonth() - birth.getMonth();

        if (
            monthDiff < 0 ||
            (monthDiff === 0 && today.getDate() < birth.getDate())
        ) {
            return age - 1;
        }
        return age;
    };

    const getInitials = (name) => {
        if (!name) return "??";
        return name
            .split(" ")
            .map((n) => n[0])
            .join("")
            .toUpperCase()
            .slice(0, 2);
    };

    const handleBackdropClick = (e) => {
        if (e.target === e.currentTarget) {
            onClose();
        }
    };

    return (
        <div
            className="fixed inset-0 z-50 overflow-y-auto animate-fadeIn"
            onClick={handleBackdropClick}
        >
            <div className="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                {/* Enhanced Background overlay */}
                <div
                    className="fixed inset-0 transition-all duration-300 bg-black/50 backdrop-blur-sm"
                    aria-hidden="true"
                ></div>

                {/* Enhanced Modal panel */}
                <div className="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white shadow-2xl rounded-2xl sm:my-8 sm:align-middle sm:max-w-6xl sm:w-full animate-scaleIn">
                    {/* Enhanced Header with Gradient */}
                    <div className="relative px-8 py-6 bg-gradient-to-r from-[#439454] via-[#4a9f5e] to-[#367a41] text-white overflow-hidden">
                        <div className="absolute inset-0 bg-black/10"></div>
                        <div className="relative flex items-center justify-between">
                            <div className="flex items-center space-x-6">
                                <div className="relative">
                                    <div className="flex items-center justify-center w-20 h-20 border shadow-2xl bg-white/20 backdrop-blur-sm rounded-2xl border-white/30">
                                        <span className="text-2xl font-bold text-white">
                                            {getInitials(employee.nama_lengkap)}
                                        </span>
                                    </div>
                                    <div className="absolute w-6 h-6 bg-green-400 border-2 border-white rounded-full -bottom-1 -right-1"></div>
                                </div>
                                <div className="space-y-1">
                                    <h3 className="text-2xl font-bold text-white">
                                        {employee.nama_lengkap}
                                    </h3>
                                    <p className="font-medium text-white/90">
                                        NIP: {employee.nip} |{" "}
                                        {employee.nama_jabatan ||
                                            employee.jabatan}
                                    </p>
                                    <p className="inline-block px-3 py-1 text-sm font-semibold text-green-100 rounded-full bg-white/20">
                                        {employee.nama_organisasi ||
                                            employee.unit_organisasi}
                                    </p>
                                </div>
                            </div>
                            <button
                                onClick={onClose}
                                className="p-3 transition-all duration-300 group rounded-xl hover:bg-white/20 hover:rotate-90 backdrop-blur-sm"
                            >
                                <X className="w-6 h-6 text-white transition-transform duration-300 group-hover:scale-110" />
                            </button>
                        </div>
                    </div>

                    {/* Enhanced Content with Better Spacing */}
                    <div className="bg-gradient-to-br from-gray-50 via-white to-gray-50 px-8 py-8 max-h-[75vh] overflow-y-auto">
                        <div className="grid grid-cols-1 gap-8 lg:grid-cols-2">
                            {/* Enhanced Personal Information */}
                            <div className="space-y-6">
                                <div className="flex items-center mb-6 space-x-3">
                                    <div className="p-3 bg-gradient-to-br from-[#439454] to-[#367a41] rounded-xl shadow-lg">
                                        <User className="w-6 h-6 text-white" />
                                    </div>
                                    <h4 className="text-xl font-bold text-gray-900">
                                        Informasi Personal
                                    </h4>
                                </div>

                                <div className="p-6 space-y-5 transition-shadow duration-300 border border-gray-200 shadow-lg rounded-2xl bg-gradient-to-br from-white to-gray-50 hover:shadow-xl">
                                    <div className="grid grid-cols-2 gap-4">
                                        <div className="space-y-2">
                                            <label className="text-sm font-semibold tracking-wide text-gray-600 uppercase">
                                                NIP
                                            </label>
                                            <p className="px-3 py-2 text-sm font-bold text-gray-900 bg-gray-100 rounded-lg">
                                                {employee.nip || "-"}
                                            </p>
                                        </div>
                                        {employee.nik && (
                                            <div className="space-y-2">
                                                <label className="text-sm font-semibold tracking-wide text-gray-600 uppercase">
                                                    NIK
                                                </label>
                                                <p className="px-3 py-2 text-sm font-bold text-gray-900 bg-gray-100 rounded-lg">
                                                    {employee.nik}
                                                </p>
                                            </div>
                                        )}
                                    </div>

                                    <div className="space-y-2">
                                        <label className="text-sm font-semibold tracking-wide text-gray-600 uppercase">
                                            Nama Lengkap
                                        </label>
                                        <p className="text-lg font-bold text-[#439454]">
                                            {employee.nama_lengkap}
                                        </p>
                                    </div>

                                    <div className="grid grid-cols-2 gap-4">
                                        <div className="space-y-2">
                                            <label className="text-sm font-semibold tracking-wide text-gray-600 uppercase">
                                                Jenis Kelamin
                                            </label>
                                            <p className="text-sm font-bold text-gray-900">
                                                {employee.jenis_kelamin === "L"
                                                    ? "Laki-laki"
                                                    : employee.jenis_kelamin ===
                                                      "P"
                                                    ? "Perempuan"
                                                    : employee.jenis_kelamin ||
                                                      "-"}
                                            </p>
                                        </div>
                                        <div className="space-y-2">
                                            <label className="text-sm font-semibold tracking-wide text-gray-600 uppercase">
                                                Usia
                                            </label>
                                            <p className="text-sm font-bold text-gray-900">
                                                {employee.usia ||
                                                    calculateAge(
                                                        employee.tanggal_lahir
                                                    )}{" "}
                                                tahun
                                            </p>
                                        </div>
                                    </div>

                                    <div className="space-y-2">
                                        <label className="text-sm font-semibold tracking-wide text-gray-600 uppercase">
                                            Tempat, Tanggal Lahir
                                        </label>
                                        <p className="text-sm font-bold text-gray-900">
                                            {employee.tempat_lahir || "-"},{" "}
                                            {formatDate(employee.tanggal_lahir)}
                                        </p>
                                    </div>

                                    {(employee.height || employee.weight) && (
                                        <div className="space-y-2">
                                            <label className="text-sm font-semibold tracking-wide text-gray-600 uppercase">
                                                Tinggi / Berat Badan
                                            </label>
                                            <div className="flex gap-3">
                                                <span className="inline-flex items-center gap-2 px-3 py-2 text-sm font-bold text-blue-800 bg-blue-100 rounded-lg">
                                                    <Ruler className="w-4 h-4" />
                                                    {employee.height || "-"} cm
                                                </span>
                                                <span className="inline-flex items-center gap-2 px-3 py-2 text-sm font-bold text-green-800 bg-green-100 rounded-lg">
                                                    <Weight className="w-4 h-4" />
                                                    {employee.weight || "-"} kg
                                                </span>
                                            </div>
                                        </div>
                                    )}
                                </div>
                            </div>

                            {/* Enhanced Job Information */}
                            <div className="space-y-6">
                                <div className="flex items-center mb-6 space-x-3">
                                    <div className="p-3 bg-gradient-to-br from-[#439454] to-[#367a41] rounded-xl shadow-lg">
                                        <Briefcase className="w-6 h-6 text-white" />
                                    </div>
                                    <h4 className="text-xl font-bold text-gray-900">
                                        Informasi Pekerjaan
                                    </h4>
                                </div>

                                <div className="p-6 space-y-5 transition-shadow duration-300 border border-gray-200 shadow-lg rounded-2xl bg-gradient-to-br from-white to-gray-50 hover:shadow-xl">
                                    <div className="space-y-2">
                                        <label className="text-sm font-semibold tracking-wide text-gray-600 uppercase">
                                            Unit Organisasi
                                        </label>
                                        <p className="px-3 py-2 text-sm font-bold text-gray-900 border border-blue-200 rounded-lg bg-blue-50">
                                            {employee.unit_organisasi || "-"}
                                        </p>
                                    </div>

                                    {employee.nama_organisasi && (
                                        <div className="space-y-2">
                                            <label className="text-sm font-semibold tracking-wide text-gray-600 uppercase">
                                                Nama Organisasi
                                            </label>
                                            <p className="text-sm font-bold text-[#439454]">
                                                {employee.nama_organisasi}
                                            </p>
                                        </div>
                                    )}

                                    <div className="space-y-2">
                                        <label className="text-sm font-semibold tracking-wide text-gray-600 uppercase">
                                            Jabatan
                                        </label>
                                        <p className="text-lg font-bold text-gray-900">
                                            {employee.nama_jabatan ||
                                                employee.jabatan ||
                                                "-"}
                                        </p>
                                    </div>

                                    {(employee.kelompok_jabatan ||
                                        employee.kelas_jabatan) && (
                                        <div className="space-y-2">
                                            <label className="text-sm font-semibold tracking-wide text-gray-600 uppercase">
                                                Kelompok / Kelas Jabatan
                                            </label>
                                            <p className="text-sm font-bold text-gray-900">
                                                {employee.kelompok_jabatan ||
                                                    "-"}{" "}
                                                /{" "}
                                                {employee.kelas_jabatan || "-"}
                                            </p>
                                        </div>
                                    )}

                                    <div className="space-y-2">
                                        <label className="text-sm font-semibold tracking-wide text-gray-600 uppercase">
                                            Status Pegawai
                                        </label>
                                        <span
                                            className={`inline-flex px-4 py-2 text-sm font-bold rounded-full shadow-md ${
                                                employee.status_pegawai ===
                                                "PEGAWAI TETAP"
                                                    ? "bg-gradient-to-r from-green-100 to-green-200 text-green-800 border border-green-300"
                                                    : "bg-gradient-to-r from-yellow-100 to-yellow-200 text-yellow-800 border border-yellow-300"
                                            }`}
                                        >
                                            {employee.status_pegawai || "-"}
                                        </span>
                                    </div>

                                    <div className="grid grid-cols-2 gap-4">
                                        <div className="space-y-2">
                                            <label className="text-sm font-semibold tracking-wide text-gray-600 uppercase">
                                                Status Kerja
                                            </label>
                                            <p className="text-sm font-bold text-gray-900">
                                                {employee.status_kerja || "-"}
                                            </p>
                                        </div>
                                        {employee.grade && (
                                            <div className="space-y-2">
                                                <label className="text-sm font-semibold tracking-wide text-gray-600 uppercase">
                                                    Grade
                                                </label>
                                                <p className="px-3 py-2 text-sm font-bold text-center text-gray-900 bg-purple-100 rounded-lg">
                                                    {employee.grade}
                                                </p>
                                            </div>
                                        )}
                                    </div>

                                    <div className="space-y-2">
                                        <label className="text-sm font-semibold tracking-wide text-gray-600 uppercase">
                                            Provider
                                        </label>
                                        <p className="text-sm font-bold text-[#439454] bg-green-50 px-3 py-2 rounded-lg border border-green-200">
                                            {employee.provider || "-"}
                                        </p>
                                    </div>

                                    <div className="space-y-2">
                                        <label className="text-sm font-semibold tracking-wide text-gray-600 uppercase">
                                            Lokasi Kerja
                                        </label>
                                        <p className="text-sm font-bold text-gray-900">
                                            {employee.lokasi_kerja || "-"}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            {/* Enhanced Contact Information */}
                            <div className="space-y-6">
                                <div className="flex items-center mb-6 space-x-3">
                                    <div className="p-3 bg-gradient-to-br from-[#439454] to-[#367a41] rounded-xl shadow-lg">
                                        <Phone className="w-6 h-6 text-white" />
                                    </div>
                                    <h4 className="text-xl font-bold text-gray-900">
                                        Informasi Kontak
                                    </h4>
                                </div>

                                <div className="p-6 space-y-5 transition-shadow duration-300 border border-gray-200 shadow-lg rounded-2xl bg-gradient-to-br from-white to-gray-50 hover:shadow-xl">
                                    <div className="space-y-2">
                                        <label className="text-sm font-semibold tracking-wide text-gray-600 uppercase">
                                            Handphone
                                        </label>
                                        <p className="px-3 py-2 text-sm font-bold text-gray-900 border border-green-200 rounded-lg bg-green-50">
                                            {employee.handphone ||
                                                employee.no_telepon ||
                                                "-"}
                                        </p>
                                    </div>

                                    {employee.email && (
                                        <div className="space-y-2">
                                            <label className="text-sm font-semibold tracking-wide text-gray-600 uppercase">
                                                Email
                                            </label>
                                            <p className="px-3 py-2 text-sm font-bold text-blue-600 border border-blue-200 rounded-lg bg-blue-50">
                                                {employee.email}
                                            </p>
                                        </div>
                                    )}

                                    <div className="space-y-2">
                                        <label className="text-sm font-semibold tracking-wide text-gray-600 uppercase">
                                            Kota Domisili
                                        </label>
                                        <p className="text-sm font-bold text-gray-900">
                                            {employee.kota_domisili || "-"}
                                        </p>
                                    </div>

                                    <div className="space-y-2">
                                        <label className="text-sm font-semibold tracking-wide text-gray-600 uppercase">
                                            Alamat
                                        </label>
                                        <p className="px-3 py-3 text-sm font-medium leading-relaxed text-gray-700 bg-gray-100 rounded-lg">
                                            {employee.alamat || "-"}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            {/* Enhanced Education Information */}
                            <div className="space-y-6">
                                <div className="flex items-center mb-6 space-x-3">
                                    <div className="p-3 bg-gradient-to-br from-[#439454] to-[#367a41] rounded-xl shadow-lg">
                                        <GraduationCap className="w-6 h-6 text-white" />
                                    </div>
                                    <h4 className="text-xl font-bold text-gray-900">
                                        Informasi Pendidikan
                                    </h4>
                                </div>

                                <div className="p-6 space-y-5 transition-shadow duration-300 border border-gray-200 shadow-lg rounded-2xl bg-gradient-to-br from-white to-gray-50 hover:shadow-xl">
                                    <div className="space-y-2">
                                        <label className="text-sm font-semibold tracking-wide text-gray-600 uppercase">
                                            Pendidikan Terakhir
                                        </label>
                                        <p className="text-lg font-bold text-[#439454]">
                                            {employee.pendidikan_terakhir ||
                                                employee.tingkat_pendidikan ||
                                                employee.pendidikan ||
                                                "-"}
                                        </p>
                                    </div>

                                    <div className="space-y-2">
                                        <label className="text-sm font-semibold tracking-wide text-gray-600 uppercase">
                                            Instansi Pendidikan
                                        </label>
                                        <p className="px-3 py-2 text-sm font-bold text-gray-900 border border-blue-200 rounded-lg bg-blue-50">
                                            {employee.instansi_pendidikan ||
                                                "-"}
                                        </p>
                                    </div>

                                    {employee.jurusan && (
                                        <div className="space-y-2">
                                            <label className="text-sm font-semibold tracking-wide text-gray-600 uppercase">
                                                Jurusan
                                            </label>
                                            <p className="text-sm font-bold text-gray-900">
                                                {employee.jurusan}
                                            </p>
                                        </div>
                                    )}

                                    {employee.tahun_lulus && (
                                        <div className="space-y-2">
                                            <label className="text-sm font-semibold tracking-wide text-gray-600 uppercase">
                                                Tahun Lulus
                                            </label>
                                            <p className="px-3 py-2 text-sm font-bold text-center text-gray-900 bg-yellow-100 border border-yellow-200 rounded-lg">
                                                {employee.tahun_lulus}
                                            </p>
                                        </div>
                                    )}

                                    {employee.remarks_pendidikan && (
                                        <div className="space-y-2">
                                            <label className="text-sm font-semibold tracking-wide text-gray-600 uppercase">
                                                Keterangan Pendidikan
                                            </label>
                                            <p className="px-3 py-3 text-sm font-medium leading-relaxed text-gray-700 bg-gray-100 rounded-lg">
                                                {employee.remarks_pendidikan}
                                            </p>
                                        </div>
                                    )}
                                </div>
                            </div>

                            {/* Enhanced Work Timeline */}
                            <div className="space-y-6">
                                <div className="flex items-center mb-6 space-x-3">
                                    <div className="p-3 bg-gradient-to-br from-[#439454] to-[#367a41] rounded-xl shadow-lg">
                                        <Clock className="w-6 h-6 text-white" />
                                    </div>
                                    <h4 className="text-xl font-bold text-gray-900">
                                        Timeline Kerja
                                    </h4>
                                </div>

                                <div className="p-6 space-y-5 transition-shadow duration-300 border border-gray-200 shadow-lg rounded-2xl bg-gradient-to-br from-white to-gray-50 hover:shadow-xl">
                                    <div className="space-y-2">
                                        <label className="text-sm font-semibold tracking-wide text-gray-600 uppercase">
                                            TMT Mulai Kerja
                                        </label>
                                        <p className="px-3 py-2 text-sm font-bold text-gray-900 border border-green-200 rounded-lg bg-green-50">
                                            {formatDate(
                                                employee.tmt_mulai_kerja
                                            )}
                                        </p>
                                    </div>

                                    <div className="space-y-2">
                                        <label className="text-sm font-semibold tracking-wide text-gray-600 uppercase">
                                            TMT Mulai Jabatan
                                        </label>
                                        <p className="px-3 py-2 text-sm font-bold text-gray-900 border border-blue-200 rounded-lg bg-blue-50">
                                            {formatDate(
                                                employee.tmt_mulai_jabatan
                                            )}
                                        </p>
                                    </div>

                                    {employee.tmt_berakhir_jabatan && (
                                        <div className="space-y-2">
                                            <label className="text-sm font-semibold tracking-wide text-gray-600 uppercase">
                                                TMT Berakhir Jabatan
                                            </label>
                                            <p className="px-3 py-2 text-sm font-bold text-gray-900 border border-orange-200 rounded-lg bg-orange-50">
                                                {formatDate(
                                                    employee.tmt_berakhir_jabatan
                                                )}
                                            </p>
                                        </div>
                                    )}

                                    {employee.tmt_berakhir_kerja && (
                                        <div className="space-y-2">
                                            <label className="text-sm font-semibold tracking-wide text-gray-600 uppercase">
                                                TMT Berakhir Kerja
                                            </label>
                                            <p className="px-3 py-2 text-sm font-bold text-gray-900 border border-red-200 rounded-lg bg-red-50">
                                                {formatDate(
                                                    employee.tmt_berakhir_kerja
                                                )}
                                            </p>
                                        </div>
                                    )}

                                    {employee.tmt_pensiun && (
                                        <div className="space-y-2">
                                            <label className="text-sm font-semibold tracking-wide text-gray-600 uppercase">
                                                TMT Pensiun
                                            </label>
                                            <p className="px-3 py-2 text-sm font-bold text-gray-900 border border-purple-200 rounded-lg bg-purple-50">
                                                {formatDate(
                                                    employee.tmt_pensiun
                                                )}
                                            </p>
                                        </div>
                                    )}

                                    {(employee.masa_kerja_tahun ||
                                        employee.masa_kerja_bulan) && (
                                        <div className="space-y-2">
                                            <label className="text-sm font-semibold tracking-wide text-gray-600 uppercase">
                                                Masa Kerja
                                            </label>
                                            <div className="flex gap-2">
                                                <span className="inline-flex items-center gap-1 px-3 py-2 text-sm font-bold text-green-800 bg-green-100 rounded-lg">
                                                    {employee.masa_kerja_tahun ||
                                                        "-"}{" "}
                                                    tahun
                                                </span>
                                                <span className="inline-flex items-center gap-1 px-3 py-2 text-sm font-bold text-blue-800 bg-blue-100 rounded-lg">
                                                    {employee.masa_kerja_bulan ||
                                                        "-"}{" "}
                                                    bulan
                                                </span>
                                            </div>
                                        </div>
                                    )}
                                </div>
                            </div>

                            {/* Enhanced Equipment & Safety dengan Seragam */}
                            <div className="space-y-6 lg:col-span-2">
                                <div className="flex items-center mb-6 space-x-3">
                                    <div className="p-3 bg-gradient-to-br from-[#439454] to-[#367a41] rounded-xl shadow-lg">
                                        <Shield className="w-6 h-6 text-white" />
                                    </div>
                                    <h4 className="text-xl font-bold text-gray-900">
                                        Perlengkapan & Keamanan
                                    </h4>
                                </div>

                                <div className="p-6 space-y-6 transition-shadow duration-300 border border-gray-200 shadow-lg rounded-2xl bg-gradient-to-br from-white to-gray-50 hover:shadow-xl">
                                    {/* Enhanced 3-Column Layout untuk Equipment */}
                                    <div className="grid grid-cols-1 gap-6 md:grid-cols-3">
                                        {/* Jenis Sepatu */}
                                        <div className="p-4 border border-gray-200 rounded-xl bg-white/50 backdrop-blur-sm">
                                            <div className="space-y-3">
                                                <div className="flex items-center gap-2 mb-2">
                                                    <div className="p-2 bg-blue-100 rounded-lg">
                                                        <Shield className="w-4 h-4 text-blue-600" />
                                                    </div>
                                                    <label className="text-sm font-semibold tracking-wide text-gray-600 uppercase">
                                                        Jenis Sepatu
                                                    </label>
                                                </div>
                                                <span
                                                    className={`inline-flex w-full justify-center px-4 py-3 text-sm font-bold rounded-xl shadow-md ${
                                                        employee.jenis_sepatu ===
                                                        "Safety Shoes"
                                                            ? "bg-gradient-to-r from-red-100 to-red-200 text-red-800 border border-red-300"
                                                            : employee.jenis_sepatu ===
                                                              "Pantofel"
                                                            ? "bg-gradient-to-r from-blue-100 to-blue-200 text-blue-800 border border-blue-300"
                                                            : "bg-gradient-to-r from-gray-100 to-gray-200 text-gray-800 border border-gray-300"
                                                    }`}
                                                >
                                                    {employee.jenis_sepatu ||
                                                        "Tidak Ditetapkan"}
                                                </span>
                                            </div>
                                        </div>

                                        {/* Ukuran Sepatu */}
                                        <div className="p-4 border border-gray-200 rounded-xl bg-white/50 backdrop-blur-sm">
                                            <div className="space-y-3">
                                                <div className="flex items-center gap-2 mb-2">
                                                    <div className="p-2 bg-gray-100 rounded-lg">
                                                        <Ruler className="w-4 h-4 text-gray-600" />
                                                    </div>
                                                    <label className="text-sm font-semibold tracking-wide text-gray-600 uppercase">
                                                        Ukuran Sepatu
                                                    </label>
                                                </div>
                                                <span className="inline-flex justify-center w-full px-4 py-3 text-sm font-bold text-gray-800 border border-gray-300 shadow-md rounded-xl bg-gradient-to-r from-gray-100 to-gray-200">
                                                    {employee.ukuran_sepatu ||
                                                        "Tidak Ditetapkan"}
                                                </span>
                                            </div>
                                        </div>

                                        {/* Seragam - Field Baru */}
                                        <div className="p-4 border border-gray-200 rounded-xl bg-white/50 backdrop-blur-sm">
                                            <div className="space-y-3">
                                                <div className="flex items-center gap-2 mb-2">
                                                    <div className="p-2 bg-green-100 rounded-lg">
                                                        <Shirt className="w-4 h-4 text-green-600" />
                                                    </div>
                                                    <label className="text-sm font-semibold tracking-wide text-gray-600 uppercase">
                                                        Seragam
                                                    </label>
                                                </div>
                                                <span
                                                    className={`inline-flex w-full justify-center px-4 py-3 text-sm font-bold rounded-xl shadow-md ${
                                                        employee.seragam &&
                                                        employee.seragam !== "-"
                                                            ? "bg-gradient-to-r from-[#439454] to-[#367a41] text-white border border-green-300"
                                                            : "bg-gradient-to-r from-gray-100 to-gray-200 text-gray-800 border border-gray-300"
                                                    }`}
                                                >
                                                    {employee.seragam &&
                                                    employee.seragam !== "-"
                                                        ? employee.seragam
                                                        : "Tidak Ditetapkan"}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    {/* BPJS Information */}
                                    {(employee.no_bpjs_kesehatan ||
                                        employee.no_bpjs_ketenagakerjaan) && (
                                        <div className="space-y-4">
                                            <div className="flex items-center gap-2 mb-4">
                                                <div className="p-2 bg-blue-100 rounded-lg">
                                                    <CreditCard className="w-4 h-4 text-blue-600" />
                                                </div>
                                                <h5 className="text-lg font-bold text-gray-900">
                                                    Informasi BPJS
                                                </h5>
                                            </div>
                                            <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                                                {employee.no_bpjs_kesehatan && (
                                                    <div className="p-4 border border-green-200 rounded-xl bg-green-50">
                                                        <div className="flex items-center gap-3">
                                                            <Heart className="w-5 h-5 text-green-600" />
                                                            <div className="flex-1">
                                                                <p className="mb-1 text-xs font-semibold tracking-wide text-green-600 uppercase">
                                                                    BPJS
                                                                    Kesehatan
                                                                </p>
                                                                <p className="text-sm font-bold text-green-800">
                                                                    {
                                                                        employee.no_bpjs_kesehatan
                                                                    }
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                )}
                                                {employee.no_bpjs_ketenagakerjaan && (
                                                    <div className="p-4 border border-blue-200 rounded-xl bg-blue-50">
                                                        <div className="flex items-center gap-3">
                                                            <CreditCard className="w-5 h-5 text-blue-600" />
                                                            <div className="flex-1">
                                                                <p className="mb-1 text-xs font-semibold tracking-wide text-blue-600 uppercase">
                                                                    BPJS
                                                                    Ketenagakerjaan
                                                                </p>
                                                                <p className="text-sm font-bold text-blue-800">
                                                                    {
                                                                        employee.no_bpjs_ketenagakerjaan
                                                                    }
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                )}
                                            </div>
                                        </div>
                                    )}

                                    {/* Kategori Karyawan */}
                                    {employee.kategori_karyawan && (
                                        <div className="space-y-2">
                                            <label className="text-sm font-semibold tracking-wide text-gray-600 uppercase">
                                                Kategori Karyawan
                                            </label>
                                            <p className="text-sm font-bold text-[#439454] bg-green-50 px-4 py-3 rounded-xl border border-green-200 text-center">
                                                {employee.kategori_karyawan}
                                            </p>
                                        </div>
                                    )}
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Enhanced Footer */}
                    <div className="flex justify-end px-8 py-6 space-x-4 border-t border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100">
                        <button
                            onClick={onClose}
                            className="group px-6 py-3 bg-white border-2 border-gray-300 rounded-xl text-sm font-semibold text-gray-700 hover:bg-gray-50 hover:border-[#439454] hover:text-[#439454] transition-all duration-300 shadow-sm hover:shadow-md transform hover:-translate-y-0.5"
                        >
                            Tutup
                        </button>
                        {employee.id && (
                            <a
                                href={`/employees/${employee.id}/edit`}
                                className="group px-6 py-3 bg-gradient-to-r from-[#439454] to-[#367a41] border-2 border-transparent rounded-xl text-sm font-semibold text-white hover:from-[#367a41] hover:to-[#2d6435] transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
                            >
                                Edit Karyawan
                            </a>
                        )}
                    </div>
                </div>
            </div>

            <style jsx>{`
                @keyframes fadeIn {
                    from {
                        opacity: 0;
                    }
                    to {
                        opacity: 1;
                    }
                }

                @keyframes scaleIn {
                    from {
                        opacity: 0;
                        transform: scale(0.9) translateY(20px);
                    }
                    to {
                        opacity: 1;
                        transform: scale(1) translateY(0);
                    }
                }

                .animate-fadeIn {
                    animation: fadeIn 0.3s ease-out;
                }

                .animate-scaleIn {
                    animation: scaleIn 0.4s ease-out;
                }
            `}</style>
        </div>
    );
};

export default EmployeeDetailModal;
