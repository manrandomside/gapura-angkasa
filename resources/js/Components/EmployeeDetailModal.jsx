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
            className="fixed inset-0 z-50 overflow-y-auto"
            onClick={handleBackdropClick}
        >
            <div className="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                {/* Background overlay */}
                <div
                    className="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"
                    aria-hidden="true"
                ></div>

                {/* Modal panel */}
                <div className="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-5xl sm:w-full">
                    {/* Header */}
                    <div className="px-6 py-4 bg-white border-b border-gray-200">
                        <div className="flex items-center justify-between">
                            <div className="flex items-center space-x-4">
                                <div className="w-16 h-16 bg-[#439454] rounded-full flex items-center justify-center">
                                    <span className="text-xl font-bold text-white">
                                        {getInitials(employee.nama_lengkap)}
                                    </span>
                                </div>
                                <div>
                                    <h3 className="text-xl font-semibold text-gray-900">
                                        {employee.nama_lengkap}
                                    </h3>
                                    <p className="text-sm text-gray-500">
                                        NIP: {employee.nip} |{" "}
                                        {employee.nama_jabatan ||
                                            employee.jabatan}
                                    </p>
                                    <p className="mt-1 text-xs font-medium text-green-600">
                                        {employee.nama_organisasi ||
                                            employee.unit_organisasi}
                                    </p>
                                </div>
                            </div>
                            <button
                                onClick={onClose}
                                className="p-2 transition-colors rounded-full hover:bg-gray-100"
                            >
                                <X className="w-6 h-6 text-gray-400" />
                            </button>
                        </div>
                    </div>

                    {/* Content */}
                    <div className="bg-white px-6 py-6 max-h-[70vh] overflow-y-auto">
                        <div className="grid grid-cols-1 gap-8 lg:grid-cols-2">
                            {/* Personal Information */}
                            <div className="space-y-4">
                                <div className="flex items-center mb-4 space-x-2">
                                    <User className="w-5 h-5 text-[#439454]" />
                                    <h4 className="text-lg font-medium text-gray-900">
                                        Informasi Personal
                                    </h4>
                                </div>

                                <div className="p-4 space-y-3 rounded-lg bg-gray-50">
                                    <div>
                                        <label className="text-sm font-medium text-gray-600">
                                            NIP
                                        </label>
                                        <p className="text-sm font-medium text-gray-900">
                                            {employee.nip || "-"}
                                        </p>
                                    </div>

                                    {employee.nik && (
                                        <div>
                                            <label className="text-sm font-medium text-gray-600">
                                                NIK
                                            </label>
                                            <p className="text-sm font-medium text-gray-900">
                                                {employee.nik}
                                            </p>
                                        </div>
                                    )}

                                    <div>
                                        <label className="text-sm font-medium text-gray-600">
                                            Nama Lengkap
                                        </label>
                                        <p className="text-sm font-medium text-gray-900">
                                            {employee.nama_lengkap}
                                        </p>
                                    </div>

                                    <div>
                                        <label className="text-sm font-medium text-gray-600">
                                            Jenis Kelamin
                                        </label>
                                        <p className="text-sm font-medium text-gray-900">
                                            {employee.jenis_kelamin === "L"
                                                ? "Laki-laki"
                                                : employee.jenis_kelamin === "P"
                                                ? "Perempuan"
                                                : employee.jenis_kelamin || "-"}
                                        </p>
                                    </div>

                                    <div>
                                        <label className="text-sm font-medium text-gray-600">
                                            Tempat, Tanggal Lahir
                                        </label>
                                        <p className="text-sm font-medium text-gray-900">
                                            {employee.tempat_lahir || "-"},{" "}
                                            {formatDate(employee.tanggal_lahir)}
                                        </p>
                                    </div>

                                    <div>
                                        <label className="text-sm font-medium text-gray-600">
                                            Usia
                                        </label>
                                        <p className="text-sm font-medium text-gray-900">
                                            {employee.usia ||
                                                calculateAge(
                                                    employee.tanggal_lahir
                                                )}{" "}
                                            tahun
                                        </p>
                                    </div>

                                    {(employee.height || employee.weight) && (
                                        <div>
                                            <label className="text-sm font-medium text-gray-600">
                                                Tinggi / Berat Badan
                                            </label>
                                            <p className="text-sm font-medium text-gray-900">
                                                {employee.height || "-"} cm /{" "}
                                                {employee.weight || "-"} kg
                                            </p>
                                        </div>
                                    )}
                                </div>
                            </div>

                            {/* Job Information */}
                            <div className="space-y-4">
                                <div className="flex items-center mb-4 space-x-2">
                                    <Building className="w-5 h-5 text-[#439454]" />
                                    <h4 className="text-lg font-medium text-gray-900">
                                        Informasi Pekerjaan
                                    </h4>
                                </div>

                                <div className="p-4 space-y-3 rounded-lg bg-gray-50">
                                    <div>
                                        <label className="text-sm font-medium text-gray-600">
                                            Unit Organisasi
                                        </label>
                                        <p className="text-sm font-medium text-gray-900">
                                            {employee.unit_organisasi || "-"}
                                        </p>
                                    </div>

                                    {employee.nama_organisasi && (
                                        <div>
                                            <label className="text-sm font-medium text-gray-600">
                                                Nama Organisasi
                                            </label>
                                            <p className="text-sm font-medium text-gray-900">
                                                {employee.nama_organisasi}
                                            </p>
                                        </div>
                                    )}

                                    <div>
                                        <label className="text-sm font-medium text-gray-600">
                                            Jabatan
                                        </label>
                                        <p className="text-sm font-medium text-gray-900">
                                            {employee.nama_jabatan ||
                                                employee.jabatan ||
                                                "-"}
                                        </p>
                                    </div>

                                    {(employee.kelompok_jabatan ||
                                        employee.kelas_jabatan) && (
                                        <div>
                                            <label className="text-sm font-medium text-gray-600">
                                                Kelompok / Kelas Jabatan
                                            </label>
                                            <p className="text-sm font-medium text-gray-900">
                                                {employee.kelompok_jabatan ||
                                                    "-"}{" "}
                                                /{" "}
                                                {employee.kelas_jabatan || "-"}
                                            </p>
                                        </div>
                                    )}

                                    <div>
                                        <label className="text-sm font-medium text-gray-600">
                                            Status Pegawai
                                        </label>
                                        <span
                                            className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${
                                                employee.status_pegawai ===
                                                "PEGAWAI TETAP"
                                                    ? "bg-green-100 text-green-800"
                                                    : "bg-yellow-100 text-yellow-800"
                                            }`}
                                        >
                                            {employee.status_pegawai || "-"}
                                        </span>
                                    </div>

                                    <div>
                                        <label className="text-sm font-medium text-gray-600">
                                            Status Kerja
                                        </label>
                                        <p className="text-sm font-medium text-gray-900">
                                            {employee.status_kerja || "-"}
                                        </p>
                                    </div>

                                    {employee.grade && (
                                        <div>
                                            <label className="text-sm font-medium text-gray-600">
                                                Grade
                                            </label>
                                            <p className="text-sm font-medium text-gray-900">
                                                {employee.grade}
                                            </p>
                                        </div>
                                    )}

                                    <div>
                                        <label className="text-sm font-medium text-gray-600">
                                            Provider
                                        </label>
                                        <p className="text-sm font-medium text-gray-900">
                                            {employee.provider || "-"}
                                        </p>
                                    </div>

                                    <div>
                                        <label className="text-sm font-medium text-gray-600">
                                            Lokasi Kerja
                                        </label>
                                        <p className="text-sm font-medium text-gray-900">
                                            {employee.lokasi_kerja || "-"}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            {/* Contact Information */}
                            <div className="space-y-4">
                                <div className="flex items-center mb-4 space-x-2">
                                    <Phone className="w-5 h-5 text-[#439454]" />
                                    <h4 className="text-lg font-medium text-gray-900">
                                        Informasi Kontak
                                    </h4>
                                </div>

                                <div className="p-4 space-y-3 rounded-lg bg-gray-50">
                                    <div>
                                        <label className="text-sm font-medium text-gray-600">
                                            Handphone
                                        </label>
                                        <p className="text-sm font-medium text-gray-900">
                                            {employee.handphone || "-"}
                                        </p>
                                    </div>

                                    {employee.no_telepon &&
                                        employee.no_telepon !==
                                            employee.handphone && (
                                            <div>
                                                <label className="text-sm font-medium text-gray-600">
                                                    No. Telepon
                                                </label>
                                                <p className="text-sm font-medium text-gray-900">
                                                    {employee.no_telepon}
                                                </p>
                                            </div>
                                        )}

                                    {employee.email && (
                                        <div>
                                            <label className="text-sm font-medium text-gray-600">
                                                Email
                                            </label>
                                            <p className="text-sm font-medium text-gray-900 text-blue-600">
                                                {employee.email}
                                            </p>
                                        </div>
                                    )}

                                    <div>
                                        <label className="text-sm font-medium text-gray-600">
                                            Kota Domisili
                                        </label>
                                        <p className="text-sm font-medium text-gray-900">
                                            {employee.kota_domisili || "-"}
                                        </p>
                                    </div>

                                    <div>
                                        <label className="text-sm font-medium text-gray-600">
                                            Alamat
                                        </label>
                                        <p className="text-sm font-medium text-gray-900">
                                            {employee.alamat || "-"}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            {/* Education Information */}
                            <div className="space-y-4">
                                <div className="flex items-center mb-4 space-x-2">
                                    <GraduationCap className="w-5 h-5 text-[#439454]" />
                                    <h4 className="text-lg font-medium text-gray-900">
                                        Informasi Pendidikan
                                    </h4>
                                </div>

                                <div className="p-4 space-y-3 rounded-lg bg-gray-50">
                                    <div>
                                        <label className="text-sm font-medium text-gray-600">
                                            Pendidikan Terakhir
                                        </label>
                                        <p className="text-sm font-medium text-gray-900">
                                            {employee.pendidikan_terakhir ||
                                                employee.pendidikan ||
                                                "-"}
                                        </p>
                                    </div>

                                    <div>
                                        <label className="text-sm font-medium text-gray-600">
                                            Instansi Pendidikan
                                        </label>
                                        <p className="text-sm font-medium text-gray-900">
                                            {employee.instansi_pendidikan ||
                                                "-"}
                                        </p>
                                    </div>

                                    {employee.jurusan && (
                                        <div>
                                            <label className="text-sm font-medium text-gray-600">
                                                Jurusan
                                            </label>
                                            <p className="text-sm font-medium text-gray-900">
                                                {employee.jurusan}
                                            </p>
                                        </div>
                                    )}

                                    {employee.tahun_lulus && (
                                        <div>
                                            <label className="text-sm font-medium text-gray-600">
                                                Tahun Lulus
                                            </label>
                                            <p className="text-sm font-medium text-gray-900">
                                                {employee.tahun_lulus}
                                            </p>
                                        </div>
                                    )}

                                    {employee.remarks_pendidikan && (
                                        <div>
                                            <label className="text-sm font-medium text-gray-600">
                                                Keterangan Pendidikan
                                            </label>
                                            <p className="text-sm font-medium text-gray-900">
                                                {employee.remarks_pendidikan}
                                            </p>
                                        </div>
                                    )}
                                </div>
                            </div>

                            {/* Work Timeline */}
                            <div className="space-y-4">
                                <div className="flex items-center mb-4 space-x-2">
                                    <Clock className="w-5 h-5 text-[#439454]" />
                                    <h4 className="text-lg font-medium text-gray-900">
                                        Timeline Kerja
                                    </h4>
                                </div>

                                <div className="p-4 space-y-3 rounded-lg bg-gray-50">
                                    <div>
                                        <label className="text-sm font-medium text-gray-600">
                                            TMT Mulai Kerja
                                        </label>
                                        <p className="text-sm font-medium text-gray-900">
                                            {formatDate(
                                                employee.tmt_mulai_kerja
                                            )}
                                        </p>
                                    </div>

                                    <div>
                                        <label className="text-sm font-medium text-gray-600">
                                            TMT Mulai Jabatan
                                        </label>
                                        <p className="text-sm font-medium text-gray-900">
                                            {formatDate(
                                                employee.tmt_mulai_jabatan
                                            )}
                                        </p>
                                    </div>

                                    {employee.tmt_berakhir_jabatan && (
                                        <div>
                                            <label className="text-sm font-medium text-gray-600">
                                                TMT Berakhir Jabatan
                                            </label>
                                            <p className="text-sm font-medium text-gray-900">
                                                {formatDate(
                                                    employee.tmt_berakhir_jabatan
                                                )}
                                            </p>
                                        </div>
                                    )}

                                    {employee.tmt_berakhir_kerja && (
                                        <div>
                                            <label className="text-sm font-medium text-gray-600">
                                                TMT Berakhir Kerja
                                            </label>
                                            <p className="text-sm font-medium text-gray-900">
                                                {formatDate(
                                                    employee.tmt_berakhir_kerja
                                                )}
                                            </p>
                                        </div>
                                    )}

                                    {employee.tmt_pensiun && (
                                        <div>
                                            <label className="text-sm font-medium text-gray-600">
                                                TMT Pensiun
                                            </label>
                                            <p className="text-sm font-medium text-gray-900">
                                                {formatDate(
                                                    employee.tmt_pensiun
                                                )}
                                            </p>
                                        </div>
                                    )}

                                    {(employee.masa_kerja_tahun ||
                                        employee.masa_kerja_bulan) && (
                                        <div>
                                            <label className="text-sm font-medium text-gray-600">
                                                Masa Kerja
                                            </label>
                                            <p className="text-sm font-medium text-gray-900">
                                                {employee.masa_kerja_tahun ||
                                                    "-"}{" "}
                                                tahun,{" "}
                                                {employee.masa_kerja_bulan ||
                                                    "-"}{" "}
                                                bulan
                                            </p>
                                        </div>
                                    )}
                                </div>
                            </div>

                            {/* Equipment & Safety */}
                            <div className="space-y-4">
                                <div className="flex items-center mb-4 space-x-2">
                                    <Shield className="w-5 h-5 text-[#439454]" />
                                    <h4 className="text-lg font-medium text-gray-900">
                                        Perlengkapan & Keamanan
                                    </h4>
                                </div>

                                <div className="p-4 space-y-3 rounded-lg bg-gray-50">
                                    <div>
                                        <label className="text-sm font-medium text-gray-600">
                                            Jenis Sepatu
                                        </label>
                                        <div className="mt-1">
                                            <span
                                                className={`inline-flex px-3 py-1 text-sm font-medium rounded-full ${
                                                    employee.jenis_sepatu ===
                                                    "Safety Shoes"
                                                        ? "bg-red-100 text-red-800"
                                                        : employee.jenis_sepatu ===
                                                          "Pantofel"
                                                        ? "bg-blue-100 text-blue-800"
                                                        : "bg-gray-100 text-gray-800"
                                                }`}
                                            >
                                                {employee.jenis_sepatu || "-"}
                                            </span>
                                        </div>
                                    </div>

                                    <div>
                                        <label className="text-sm font-medium text-gray-600">
                                            Ukuran Sepatu
                                        </label>
                                        <div className="mt-1">
                                            <span className="inline-flex px-3 py-1 text-sm font-medium text-gray-800 bg-gray-100 rounded-full">
                                                {employee.ukuran_sepatu || "-"}
                                            </span>
                                        </div>
                                    </div>

                                    {(employee.no_bpjs_kesehatan ||
                                        employee.no_bpjs_ketenagakerjaan) && (
                                        <div>
                                            <label className="text-sm font-medium text-gray-600">
                                                BPJS
                                            </label>
                                            <div className="space-y-1 text-sm font-medium text-gray-900">
                                                {employee.no_bpjs_kesehatan && (
                                                    <p>
                                                        Kesehatan:{" "}
                                                        {
                                                            employee.no_bpjs_kesehatan
                                                        }
                                                    </p>
                                                )}
                                                {employee.no_bpjs_ketenagakerjaan && (
                                                    <p>
                                                        Ketenagakerjaan:{" "}
                                                        {
                                                            employee.no_bpjs_ketenagakerjaan
                                                        }
                                                    </p>
                                                )}
                                            </div>
                                        </div>
                                    )}

                                    {employee.kategori_karyawan && (
                                        <div>
                                            <label className="text-sm font-medium text-gray-600">
                                                Kategori Karyawan
                                            </label>
                                            <p className="text-sm font-medium text-gray-900">
                                                {employee.kategori_karyawan}
                                            </p>
                                        </div>
                                    )}
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Footer */}
                    <div className="flex justify-end px-6 py-4 space-x-3 bg-gray-50">
                        <button
                            onClick={onClose}
                            className="px-4 py-2 bg-white border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#439454] transition-colors duration-200"
                        >
                            Tutup
                        </button>
                        {employee.id && (
                            <a
                                href={`/employees/${employee.id}/edit`}
                                className="px-4 py-2 bg-[#439454] border border-transparent rounded-md text-sm font-medium text-white hover:bg-[#367a41] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#439454] transition-colors duration-200"
                            >
                                Edit Karyawan
                            </a>
                        )}
                    </div>
                </div>
            </div>
        </div>
    );
};

export default EmployeeDetailModal;
