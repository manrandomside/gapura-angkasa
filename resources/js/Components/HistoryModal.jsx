import React, { useState, useEffect } from "react";
import {
    X,
    Calendar,
    Building2,
    Users,
    UserCheck,
    Clock,
    Briefcase,
    ChevronRight,
    Star,
} from "lucide-react";
import axios from "axios";

const HistoryModal = ({ isOpen, onClose }) => {
    const [historyData, setHistoryData] = useState([]);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);

    // Fetch history data when modal opens
    useEffect(() => {
        if (isOpen) {
            fetchHistoryData();
        }
    }, [isOpen]);

    const fetchHistoryData = async () => {
        setLoading(true);
        setError(null);

        try {
            console.log("Fetching employee history...");
            const response = await axios.get("/api/dashboard/employee-history");
            console.log("API Response:", response.data);

            if (response.data.success) {
                setHistoryData(response.data.history || []);
            } else {
                setError("Gagal mengambil data history");
                console.error("API Error:", response.data);
            }
        } catch (error) {
            console.error("Error fetching history:", error);
            if (error.response) {
                console.error("Response data:", error.response.data);
                console.error("Response status:", error.response.status);
            }
            setError("Terjadi kesalahan saat mengambil data history");
        } finally {
            setLoading(false);
        }
    };

    // Format date untuk display
    const formatDate = (dateString) => {
        if (!dateString) return "Tanggal tidak tersedia";

        try {
            const date = new Date(dateString);
            return date.toLocaleDateString("id-ID", {
                weekday: "long",
                year: "numeric",
                month: "long",
                day: "numeric",
                hour: "2-digit",
                minute: "2-digit",
            });
        } catch (error) {
            return "Tanggal tidak valid";
        }
    };

    // Get employee initials
    const getEmployeeInitial = (employee) => {
        if (!employee || !employee.nama_lengkap) return "N";

        const words = employee.nama_lengkap.split(" ");
        if (words.length === 1) {
            return words[0].charAt(0).toUpperCase();
        }
        return (words[0].charAt(0) + words[1].charAt(0)).toUpperCase();
    };

    // Format organizational structure
    const formatOrganizationalStructure = (structure) => {
        if (!structure) return "Struktur tidak tersedia";

        const parts = [];

        if (structure.unit_organisasi) {
            parts.push(structure.unit_organisasi);
        }

        if (structure.unit && structure.unit.name) {
            parts.push(structure.unit.name);
        }

        if (structure.sub_unit && structure.sub_unit.name) {
            parts.push(structure.sub_unit.name);
        }

        return parts.length > 0 ? parts.join(" > ") : "Struktur tidak tersedia";
    };

    // Get status badge color
    const getStatusBadgeColor = (status) => {
        switch (status?.toLowerCase()) {
            case "pegawai tetap":
                return "bg-green-100 text-green-800 border-green-200";
            case "pkwt":
                return "bg-blue-100 text-blue-800 border-blue-200";
            case "tad":
            case "tad paket sdm":
                return "bg-orange-100 text-orange-800 border-orange-200";
            default:
                return "bg-gray-100 text-gray-800 border-gray-200";
        }
    };

    // Get job position badge color
    const getPositionBadgeColor = (position) => {
        switch (position?.toLowerCase()) {
            case "supervisor":
                return "bg-purple-100 text-purple-800 border-purple-200";
            case "manager":
                return "bg-indigo-100 text-indigo-800 border-indigo-200";
            case "staff":
                return "bg-cyan-100 text-cyan-800 border-cyan-200";
            default:
                return "bg-gray-100 text-gray-800 border-gray-200";
        }
    };

    if (!isOpen) return null;

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm">
            <div className="relative w-full max-w-6xl max-h-[90vh] mx-4 bg-white rounded-2xl shadow-2xl overflow-hidden">
                {/* Header */}
                <div className="flex items-center justify-between p-6 bg-gradient-to-r from-[#439454] to-[#367a41] text-white">
                    <div className="flex items-center gap-3">
                        <div className="p-2 bg-white rounded-lg bg-opacity-20">
                            <Clock className="w-6 h-6" />
                        </div>
                        <div>
                            <h2 className="text-2xl font-bold">
                                History Data Karyawan
                            </h2>
                            <p className="text-green-100">
                                Data karyawan yang baru ditambahkan (30 hari
                                terakhir)
                            </p>
                        </div>
                    </div>
                    <button
                        onClick={onClose}
                        className="p-2 transition-all duration-200 bg-white rounded-lg bg-opacity-20 hover:bg-white hover:bg-opacity-30"
                    >
                        <X className="w-6 h-6" />
                    </button>
                </div>

                {/* Content */}
                <div className="flex-1 overflow-auto max-h-[calc(90vh-80px)]">
                    {loading ? (
                        <div className="flex flex-col items-center justify-center py-16">
                            <div className="w-12 h-12 border-4 border-[#439454] border-t-transparent rounded-full animate-spin mb-4"></div>
                            <p className="text-gray-600">
                                Mengambil data history...
                            </p>
                        </div>
                    ) : error ? (
                        <div className="flex flex-col items-center justify-center py-16">
                            <div className="p-4 mb-4 bg-red-100 rounded-full">
                                <X className="w-8 h-8 text-red-600" />
                            </div>
                            <h3 className="mb-2 text-lg font-semibold text-red-800">
                                Terjadi Kesalahan
                            </h3>
                            <p className="mb-4 text-sm text-red-600">{error}</p>
                            <button
                                onClick={fetchHistoryData}
                                className="px-6 py-2 bg-[#439454] text-white rounded-lg hover:bg-[#367a41] transition-colors"
                            >
                                Coba Lagi
                            </button>
                        </div>
                    ) : historyData.length === 0 ? (
                        <div className="flex flex-col items-center justify-center py-16">
                            <div className="p-4 mb-4 bg-gray-100 rounded-full">
                                <Users className="w-8 h-8 text-gray-400" />
                            </div>
                            <h3 className="mb-2 text-lg font-semibold text-gray-700">
                                Belum Ada Data History
                            </h3>
                            <p className="max-w-md text-sm text-center text-gray-500">
                                Belum ada karyawan baru yang ditambahkan dalam
                                30 hari terakhir.
                            </p>
                        </div>
                    ) : (
                        <div className="p-6">
                            <div className="mb-4 text-sm text-gray-600">
                                Menampilkan {historyData.length} karyawan baru
                                dalam 30 hari terakhir
                            </div>

                            <div className="grid gap-4">
                                {historyData.map((employee, index) => (
                                    <div
                                        key={employee.id || index}
                                        className="bg-white border border-gray-200 rounded-xl p-6 hover:shadow-md hover:border-[#439454] transition-all duration-300"
                                    >
                                        <div className="flex items-start gap-4">
                                            {/* Avatar */}
                                            <div className="flex-shrink-0">
                                                <div className="w-16 h-16 bg-gradient-to-r from-[#439454] to-[#367a41] rounded-full flex items-center justify-center text-white font-bold text-lg">
                                                    {getEmployeeInitial(
                                                        employee
                                                    )}
                                                </div>
                                            </div>

                                            {/* Employee Info */}
                                            <div className="flex-1 min-w-0">
                                                <div className="flex items-start justify-between mb-3">
                                                    <div>
                                                        <h3 className="mb-1 text-xl font-bold text-gray-900">
                                                            {
                                                                employee.nama_lengkap
                                                            }
                                                        </h3>
                                                        <div className="flex items-center gap-2 mb-2">
                                                            <span className="text-sm text-gray-600">
                                                                NIP:{" "}
                                                                {employee.nip ||
                                                                    "Tidak tersedia"}
                                                            </span>
                                                            {employee.nik && (
                                                                <>
                                                                    <span className="text-gray-400">
                                                                        •
                                                                    </span>
                                                                    <span className="text-sm text-gray-600">
                                                                        NIK:{" "}
                                                                        {
                                                                            employee.nik
                                                                        }
                                                                    </span>
                                                                </>
                                                            )}
                                                        </div>
                                                    </div>

                                                    {/* Date Badge */}
                                                    <div className="flex flex-col items-end">
                                                        <div className="px-3 py-1 bg-[#439454] bg-opacity-10 text-[#439454] rounded-full text-sm font-medium mb-1">
                                                            {employee.relative_date ||
                                                                formatDate(
                                                                    employee.created_at
                                                                )}
                                                        </div>
                                                        <div className="text-xs text-gray-500">
                                                            {employee.formatted_date ||
                                                                formatDate(
                                                                    employee.created_at
                                                                )}
                                                        </div>
                                                    </div>
                                                </div>

                                                {/* Organizational Structure */}
                                                <div className="mb-3">
                                                    <div className="flex items-center gap-2 mb-2">
                                                        <Building2 className="w-4 h-4 text-gray-400" />
                                                        <span className="text-sm font-medium text-gray-700">
                                                            Struktur Organisasi
                                                        </span>
                                                    </div>
                                                    <div className="pl-6">
                                                        <p className="text-sm text-gray-600">
                                                            {formatOrganizationalStructure(
                                                                employee.organizational_structure
                                                            )}
                                                        </p>
                                                    </div>
                                                </div>

                                                {/* Job Information */}
                                                <div className="mb-3">
                                                    <div className="flex items-center gap-2 mb-2">
                                                        <Briefcase className="w-4 h-4 text-gray-400" />
                                                        <span className="text-sm font-medium text-gray-700">
                                                            Informasi Jabatan
                                                        </span>
                                                    </div>
                                                    <div className="pl-6">
                                                        <p className="mb-1 text-sm font-medium text-gray-900">
                                                            {employee.jabatan ||
                                                                "Jabatan tidak tersedia"}
                                                        </p>
                                                        {employee.kelompok_jabatan && (
                                                            <span
                                                                className={`inline-block px-2 py-1 rounded-md text-xs border ${getPositionBadgeColor(
                                                                    employee.kelompok_jabatan
                                                                )}`}
                                                            >
                                                                {
                                                                    employee.kelompok_jabatan
                                                                }
                                                            </span>
                                                        )}
                                                    </div>
                                                </div>

                                                {/* Status Information */}
                                                <div className="flex items-center gap-3">
                                                    <div className="flex items-center gap-2">
                                                        <UserCheck className="w-4 h-4 text-gray-400" />
                                                        <span className="text-sm font-medium text-gray-700">
                                                            Status:
                                                        </span>
                                                    </div>
                                                    <span
                                                        className={`px-2 py-1 rounded-md text-xs border ${getStatusBadgeColor(
                                                            employee.status_pegawai
                                                        )}`}
                                                    >
                                                        {employee.status_pegawai ||
                                                            "Status tidak tersedia"}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>
                    )}
                </div>

                {/* Footer */}
                <div className="px-6 py-4 border-t border-gray-200 bg-gray-50">
                    <p className="text-xs text-center text-gray-500">
                        Data history karyawan yang baru ditambahkan dalam 30
                        hari terakhir
                    </p>
                </div>
            </div>
        </div>
    );
};

export default HistoryModal;
