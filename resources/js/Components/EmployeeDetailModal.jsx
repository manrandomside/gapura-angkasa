import React from "react";
import {
    X,
    User,
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
    Clock,
    Star,
    CreditCard,
    Ruler,
    Weight,
    Heart,
    Building,
} from "lucide-react";

const EmployeeDetailModal = ({ employee, isOpen, onClose }) => {
    if (!isOpen || !employee) return null;

    // Helper function untuk get unit name dari relasi database
    const getUnitName = () => {
        if (employee.unit && employee.unit.name) {
            return employee.unit.name;
        }
        return employee.unit_id || "-";
    };

    // Helper function untuk get sub unit name dari relasi database
    const getSubUnitName = () => {
        if (employee.sub_unit && employee.sub_unit.name) {
            return employee.sub_unit.name;
        }
        // Check if unit organisasi has no sub units (EGM, GM)
        if (["EGM", "GM"].includes(employee.unit_organisasi)) {
            return "-";
        }
        return employee.sub_unit_id || "-";
    };

    // Helper function untuk format display struktur organisasi lengkap
    const getOrganizationStructure = () => {
        const parts = [];

        if (employee.unit_organisasi) {
            parts.push(employee.unit_organisasi);
        }

        const unitName = getUnitName();
        if (unitName && unitName !== "-") {
            parts.push(unitName);
        }

        const subUnitName = getSubUnitName();
        if (subUnitName && subUnitName !== "-") {
            parts.push(subUnitName);
        }

        return parts.join(" → ");
    };

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

    // FIXED: Fungsi calculateMasaKerja yang konsisten dengan Edit.jsx
    const calculateMasaKerja = (tmtMulaiKerja, tmtBerakhirKerja = null) => {
        if (!tmtMulaiKerja) return "-";

        const startDate = new Date(tmtMulaiKerja);
        const endDate = tmtBerakhirKerja
            ? new Date(tmtBerakhirKerja)
            : new Date();

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

    const getInitials = (name) => {
        if (!name) return "??";
        return name
            .split(" ")
            .map((n) => n[0])
            .join("")
            .toUpperCase()
            .slice(0, 2);
    };

    const formatGender = (gender) => {
        if (!gender) return "-";
        if (gender === "L" || gender === "Laki-laki") return "Laki-laki";
        if (gender === "P" || gender === "Perempuan") return "Perempuan";
        return gender;
    };

    const handleBackdropClick = (e) => {
        if (e.target === e.currentTarget) {
            onClose();
        }
    };

    const DetailCard = ({ title, icon: Icon, children, className = "" }) => (
        <div
            className={`p-6 space-y-5 transition-shadow duration-300 border border-gray-200 shadow-lg rounded-2xl bg-gradient-to-br from-white to-gray-50 hover:shadow-xl ${className}`}
        >
            <div className="flex items-center mb-4 space-x-3">
                <div className="p-3 bg-gradient-to-br from-[#439454] to-[#367a41] rounded-xl shadow-lg">
                    <Icon className="w-5 h-5 text-white" />
                </div>
                <h4 className="text-lg font-bold text-gray-900">{title}</h4>
            </div>
            {children}
        </div>
    );

    const FieldRow = ({ label, value, colSpan = 1 }) => (
        <div
            className={`space-y-2 ${
                colSpan === 2 ? "col-span-2" : colSpan === 3 ? "col-span-3" : ""
            }`}
        >
            <label className="text-sm font-semibold tracking-wide text-gray-600 uppercase">
                {label}
            </label>
            <p className="px-3 py-2 text-sm font-medium text-gray-900 bg-gray-100 rounded-lg">
                {value || "-"}
            </p>
        </div>
    );

    return (
        <div
            className="fixed inset-0 z-50 overflow-y-auto animate-fadeIn"
            onClick={handleBackdropClick}
        >
            <div className="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div
                    className="fixed inset-0 transition-all duration-300 bg-black/50 backdrop-blur-sm"
                    aria-hidden="true"
                ></div>

                <div className="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white shadow-2xl rounded-2xl sm:my-8 sm:align-middle sm:max-w-7xl sm:w-full animate-scaleIn">
                    {/* Header dengan informasi utama */}
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
                                <div className="space-y-2">
                                    <h3 className="text-3xl font-bold text-white">
                                        {employee.nama_lengkap || "-"}
                                    </h3>
                                    <p className="text-xl font-medium text-white/90">
                                        NIP: {employee.nip || "-"}
                                    </p>

                                    {/* Breakdown detail struktur organisasi */}
                                    <div className="flex flex-wrap gap-2 text-sm">
                                        {employee.unit_organisasi && (
                                            <span className="inline-block px-3 py-1 font-semibold text-green-100 rounded-full bg-white/20">
                                                {employee.unit_organisasi}
                                            </span>
                                        )}
                                        {getUnitName() !== "-" && (
                                            <span className="inline-block px-3 py-1 font-semibold text-blue-100 rounded-full bg-white/20">
                                                Unit: {getUnitName()}
                                            </span>
                                        )}
                                        {getSubUnitName() !== "-" &&
                                            !["EGM", "GM"].includes(
                                                employee.unit_organisasi
                                            ) && (
                                                <span className="inline-block px-3 py-1 font-semibold text-purple-100 rounded-full bg-white/20">
                                                    Sub Unit: {getSubUnitName()}
                                                </span>
                                            )}
                                    </div>
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

                    {/* Content */}
                    <div className="bg-gradient-to-br from-gray-50 via-white to-gray-50 px-8 py-8 max-h-[80vh] overflow-y-auto">
                        <div className="space-y-8">
                            {/* 1. Data Pekerjaan - PALING ATAS dengan urutan yang diminta */}
                            <DetailCard title="Data Pekerjaan" icon={Building2}>
                                <div className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                                    {/* Urutan sesuai permintaan */}
                                    <FieldRow
                                        label="Unit Organisasi"
                                        value={employee.unit_organisasi}
                                    />
                                    <FieldRow
                                        label="Unit"
                                        value={getUnitName()}
                                    />
                                    <FieldRow
                                        label="Sub Unit"
                                        value={getSubUnitName()}
                                    />
                                    <FieldRow
                                        label="Nama Jabatan"
                                        value={employee.nama_jabatan}
                                    />
                                    <FieldRow
                                        label="Status Pegawai"
                                        value={employee.status_pegawai}
                                    />
                                    <FieldRow
                                        label="Kelompok Jabatan"
                                        value={employee.kelompok_jabatan}
                                    />
                                    <FieldRow
                                        label="Provider"
                                        value={employee.provider}
                                    />
                                    <FieldRow
                                        label="TMT Mulai Kerja"
                                        value={formatDate(
                                            employee.tmt_mulai_kerja
                                        )}
                                    />
                                    <FieldRow
                                        label="TMT Berakhir Kerja"
                                        value={formatDate(
                                            employee.tmt_berakhir_kerja
                                        )}
                                    />
                                    <FieldRow
                                        label="TMT Mulai Jabatan"
                                        value={formatDate(
                                            employee.tmt_mulai_jabatan
                                        )}
                                    />
                                    <FieldRow
                                        label="TMT Akhir Jabatan"
                                        value={formatDate(
                                            employee.tmt_akhir_jabatan ||
                                                employee.tmt_berakhir_jabatan
                                        )}
                                    />
                                    <FieldRow
                                        label="Status Kerja"
                                        value={employee.status_kerja}
                                    />
                                    <FieldRow
                                        label="Unit Kerja Sesuai Kontrak"
                                        value={employee.unit_kerja_kontrak}
                                    />
                                    <FieldRow
                                        label="Grade"
                                        value={employee.grade}
                                    />
                                    <FieldRow
                                        label="Lokasi Kerja"
                                        value={employee.lokasi_kerja}
                                    />
                                    <FieldRow
                                        label="Cabang"
                                        value={employee.cabang}
                                    />
                                    <FieldRow
                                        label="Masa Kerja"
                                        value={
                                            employee.masa_kerja ||
                                            calculateMasaKerja(
                                                employee.tmt_mulai_kerja,
                                                employee.tmt_berakhir_kerja
                                            )
                                        }
                                    />
                                    <FieldRow
                                        label="TMT Pensiun"
                                        value={formatDate(employee.tmt_pensiun)}
                                    />
                                </div>
                            </DetailCard>

                            {/* 2. Data Pribadi */}
                            <DetailCard title="Data Pribadi" icon={User}>
                                <div className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                                    <FieldRow
                                        label="NIK"
                                        value={employee.nik}
                                    />
                                    <FieldRow
                                        label="NIP"
                                        value={employee.nip}
                                    />
                                    <FieldRow
                                        label="Nama Lengkap"
                                        value={employee.nama_lengkap}
                                    />
                                    <FieldRow
                                        label="Jenis Kelamin"
                                        value={formatGender(
                                            employee.jenis_kelamin
                                        )}
                                    />
                                    <FieldRow
                                        label="Tempat Lahir"
                                        value={employee.tempat_lahir}
                                    />
                                    <FieldRow
                                        label="Tanggal Lahir"
                                        value={formatDate(
                                            employee.tanggal_lahir
                                        )}
                                    />
                                    <FieldRow
                                        label="Usia"
                                        value={
                                            employee.usia
                                                ? `${employee.usia} tahun`
                                                : "-"
                                        }
                                    />
                                    <FieldRow
                                        label="Kota Domisili"
                                        value={employee.kota_domisili}
                                    />
                                    <FieldRow
                                        label="Alamat Lengkap"
                                        value={
                                            employee.alamat_lengkap ||
                                            employee.alamat
                                        }
                                        colSpan={3}
                                    />
                                    <FieldRow
                                        label="Handphone"
                                        value={
                                            employee.handphone ||
                                            employee.no_telepon
                                        }
                                    />
                                    <FieldRow
                                        label="Email"
                                        value={employee.email}
                                    />
                                    <FieldRow
                                        label="No. BPJS Kesehatan"
                                        value={employee.no_bpjs_kesehatan}
                                    />
                                    <FieldRow
                                        label="No. BPJS Ketenagakerjaan"
                                        value={employee.no_bpjs_ketenagakerjaan}
                                    />
                                    <FieldRow
                                        label="Tinggi Badan"
                                        value={
                                            employee.height
                                                ? `${employee.height} cm`
                                                : "-"
                                        }
                                    />
                                    <FieldRow
                                        label="Berat Badan"
                                        value={
                                            employee.weight
                                                ? `${employee.weight} kg`
                                                : "-"
                                        }
                                    />
                                </div>
                            </DetailCard>

                            {/* 3. Data Pendidikan */}
                            <DetailCard
                                title="Data Pendidikan"
                                icon={GraduationCap}
                            >
                                <div className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                                    <FieldRow
                                        label="Pendidikan Terakhir"
                                        value={
                                            employee.pendidikan_terakhir ||
                                            employee.tingkat_pendidikan
                                        }
                                    />
                                    <FieldRow
                                        label="Instansi Pendidikan"
                                        value={employee.instansi_pendidikan}
                                    />
                                    <FieldRow
                                        label="Jurusan"
                                        value={employee.jurusan}
                                    />
                                    <FieldRow
                                        label="Tahun Lulus"
                                        value={employee.tahun_lulus}
                                    />
                                </div>
                            </DetailCard>

                            {/* 4. Data Tambahan */}
                            <DetailCard title="Data Tambahan" icon={FileText}>
                                <div className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                                    <FieldRow
                                        label="Seragam"
                                        value={employee.seragam}
                                    />
                                    <FieldRow
                                        label="Jenis Sepatu"
                                        value={employee.jenis_sepatu}
                                    />
                                    <FieldRow
                                        label="Ukuran Sepatu"
                                        value={employee.ukuran_sepatu}
                                    />
                                </div>
                            </DetailCard>
                        </div>
                    </div>

                    {/* Footer */}
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
