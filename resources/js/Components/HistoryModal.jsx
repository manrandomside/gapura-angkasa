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
    Search,
    Filter,
} from "lucide-react";
import axios from "axios";

const HistoryModal = ({ isOpen, onClose }) => {
    const [historyData, setHistoryData] = useState([]);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);
    const [searchTerm, setSearchTerm] = useState("");
    const [filterUnit, setFilterUnit] = useState("all");
    const [filterStatus, setFilterStatus] = useState("all");

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
            const response = await axios.get("/api/dashboard/employee-history");
            if (response.data.success) {
                setHistoryData(response.data.history || []);
            } else {
                setError("Gagal mengambil data history");
            }
        } catch (error) {
            console.error("Error fetching history:", error);
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

        if (employee.initials) {
            return employee.initials;
        }

        const name = employee.nama_lengkap;
        const words = name.split(" ");
        if (words.length === 1) {
            return name.charAt(0).toUpperCase();
        }
        return (words[0].charAt(0) + words[1].charAt(0)).toUpperCase();
    };

    // Get organizational structure display
    const getOrganizationalDisplay = (employee) => {
        if (!employee || !employee.organizational_structure) {
            return "Struktur organisasi tidak tersedia";
        }

        const structure = employee.organizational_structure;
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

        return parts.length > 0 ? parts.join(" → ") : "Struktur tidak lengkap";
    };

    // Get status badge color
    const getStatusBadgeColor = (status) => {
        switch (status) {
            case "PEGAWAI TETAP":
                return "bg-green-100 text-green-800 border-green-200";
            case "PKWT":
                return "bg-blue-100 text-blue-800 border-blue-200";
            case "TAD PAKET SDM":
                return "bg-yellow-100 text-yellow-800 border-yellow-200";
            case "TAD PAKET PEKERJAAN":
                return "bg-orange-100 text-orange-800 border-orange-200";
            default:
                return "bg-gray-100 text-gray-800 border-gray-200";
        }
    };

    // Filter data based on search and filters
    const filteredData = historyData.filter((employee) => {
        const matchSearch =
            !searchTerm ||
            employee.nama_lengkap
                ?.toLowerCase()
                .includes(searchTerm.toLowerCase()) ||
            employee.nip?.toLowerCase().includes(searchTerm.toLowerCase()) ||
            employee.nik?.toLowerCase().includes(searchTerm.toLowerCase());

        const matchUnit =
            filterUnit === "all" ||
            employee.organizational_structure?.unit_organisasi === filterUnit;

        const matchStatus =
            filterStatus === "all" || employee.status_pegawai === filterStatus;

        return matchSearch && matchUnit && matchStatus;
    });

    // Get unique unit organisasi for filter
    const uniqueUnits = [
        ...new Set(
            historyData
                .map((emp) => emp.organizational_structure?.unit_organisasi)
                .filter(Boolean)
        ),
    ];
    const uniqueStatuses = [
        ...new Set(
            historyData.map((emp) => emp.status_pegawai).filter(Boolean)
        ),
    ];

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

                {/* Search and Filter Bar */}
                <div className="p-6 border-b border-gray-200 bg-gray-50">
                    <div className="flex flex-col gap-4 md:flex-row">
                        {/* Search */}
                        <div className="relative flex-1">
                            <Search className="absolute w-5 h-5 text-gray-400 transform -translate-y-1/2 left-3 top-1/2" />
                            <input
                                type="text"
                                placeholder="Cari berdasarkan nama, NIP, atau NIK..."
                                value={searchTerm}
                                onChange={(e) => setSearchTerm(e.target.value)}
                                className="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#439454] focus:border-[#439454] transition-all duration-200"
                            />
                        </div>

                        {/* Filter Unit */}
                        <select
                            value={filterUnit}
                            onChange={(e) => setFilterUnit(e.target.value)}
                            className="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#439454] focus:border-[#439454] transition-all duration-200"
                        >
                            <option value="all">Semua Unit Organisasi</option>
                            {uniqueUnits.map((unit) => (
                                <option key={unit} value={unit}>
                                    {unit}
                                </option>
                            ))}
                        </select>

                        {/* Filter Status */}
                        <select
                            value={filterStatus}
                            onChange={(e) => setFilterStatus(e.target.value)}
                            className="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#439454] focus:border-[#439454] transition-all duration-200"
                        >
                            <option value="all">Semua Status</option>
                            {uniqueStatuses.map((status) => (
                                <option key={status} value={status}>
                                    {status}
                                </option>
                            ))}
                        </select>
                    </div>
                </div>

                {/* Content */}
                <div className="flex-1 overflow-y-auto max-h-[60vh]">
                    {loading ? (
                        <div className="flex items-center justify-center p-12">
                            <div className="flex items-center gap-3">
                                <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-[#439454]"></div>
                                <span className="font-medium text-gray-600">
                                    Memuat data history...
                                </span>
                            </div>
                        </div>
                    ) : error ? (
                        <div className="flex items-center justify-center p-12">
                            <div className="text-center">
                                <div className="flex items-center justify-center w-16 h-16 mx-auto mb-4 bg-red-100 rounded-full">
                                    <X className="w-8 h-8 text-red-500" />
                                </div>
                                <h3 className="mb-2 text-lg font-semibold text-gray-900">
                                    Terjadi Kesalahan
                                </h3>
                                <p className="mb-4 text-gray-600">{error}</p>
                                <button
                                    onClick={fetchHistoryData}
                                    className="px-4 py-2 bg-[#439454] text-white rounded-lg hover:bg-[#367a41] transition-colors duration-200"
                                >
                                    Coba Lagi
                                </button>
                            </div>
                        </div>
                    ) : filteredData.length === 0 ? (
                        <div className="flex items-center justify-center p-12">
                            <div className="text-center">
                                <div className="flex items-center justify-center w-16 h-16 mx-auto mb-4 bg-gray-100 rounded-full">
                                    <Users className="w-8 h-8 text-gray-400" />
                                </div>
                                <h3 className="mb-2 text-lg font-semibold text-gray-900">
                                    Tidak Ada Data History
                                </h3>
                                <p className="text-gray-600">
                                    {searchTerm ||
                                    filterUnit !== "all" ||
                                    filterStatus !== "all"
                                        ? "Tidak ada data yang sesuai dengan filter yang dipilih."
                                        : "Belum ada karyawan baru yang ditambahkan dalam 30 hari terakhir."}
                                </p>
                            </div>
                        </div>
                    ) : (
                        <div className="p-6">
                            <div className="mb-4 text-sm text-gray-600">
                                Menampilkan {filteredData.length} dari{" "}
                                {historyData.length} data karyawan
                            </div>

                            <div className="grid gap-4">
                                {filteredData.map((employee, index) => (
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
                                                            {employee.nama_lengkap ||
                                                                "Nama tidak tersedia"}
                                                        </h3>
                                                        <div className="flex items-center gap-4 text-sm text-gray-600">
                                                            <span className="font-medium">
                                                                NIP:{" "}
                                                                {employee.nip ||
                                                                    "Tidak ada"}
                                                            </span>
                                                            <span className="font-medium">
                                                                NIK:{" "}
                                                                {employee.nik ||
                                                                    "Tidak ada"}
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <span
                                                        className={`inline-flex items-center px-3 py-1 rounded-full text-sm font-medium border ${getStatusBadgeColor(
                                                            employee.status_pegawai
                                                        )}`}
                                                    >
                                                        {employee.status_pegawai ||
                                                            "Status tidak tersedia"}
                                                    </span>
                                                </div>

                                                {/* Organizational Structure */}
                                                <div className="p-4 mb-4 rounded-lg bg-gray-50">
                                                    <div className="flex items-center gap-2 mb-2">
                                                        <Building2 className="w-4 h-4 text-[#439454]" />
                                                        <span className="font-semibold text-gray-700">
                                                            Struktur Organisasi
                                                        </span>
                                                    </div>
                                                    <p className="font-medium text-gray-800">
                                                        {getOrganizationalDisplay(
                                                            employee
                                                        )}
                                                    </p>
                                                </div>

                                                {/* Job Information */}
                                                {(employee.jabatan ||
                                                    employee.kelompok_jabatan) && (
                                                    <div className="flex items-center gap-4 mb-4">
                                                        <div className="flex items-center gap-2">
                                                            <Briefcase className="w-4 h-4 text-[#439454]" />
                                                            <span className="font-medium text-gray-700">
                                                                Jabatan:
                                                            </span>
                                                            <span className="text-gray-900">
                                                                {employee.jabatan ||
                                                                    "Tidak tersedia"}
                                                            </span>
                                                        </div>
                                                        {employee.kelompok_jabatan && (
                                                            <div className="flex items-center gap-2">
                                                                <span className="font-medium text-gray-700">
                                                                    Kelompok:
                                                                </span>
                                                                <span className="text-gray-900">
                                                                    {
                                                                        employee.kelompok_jabatan
                                                                    }
                                                                </span>
                                                            </div>
                                                        )}
                                                    </div>
                                                )}

                                                {/* Date Added */}
                                                <div className="flex items-center gap-2 text-sm">
                                                    <Calendar className="w-4 h-4 text-[#439454]" />
                                                    <span className="font-medium text-gray-700">
                                                        Ditambahkan pada:
                                                    </span>
                                                    <span className="font-medium text-gray-900">
                                                        {formatDate(
                                                            employee.created_at
                                                        )}
                                                    </span>
                                                    <span className="text-gray-500">
                                                        (
                                                        {employee.relative_date ||
                                                            "Baru saja"}
                                                        )
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
                    <div className="flex items-center justify-between">
                        <p className="text-sm text-gray-600">
                            Data history karyawan yang baru ditambahkan dalam 30
                            hari terakhir
                        </p>
                        <button
                            onClick={onClose}
                            className="px-6 py-2 font-medium text-white transition-colors duration-200 bg-gray-600 rounded-lg hover:bg-gray-700"
                        >
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default HistoryModal;
