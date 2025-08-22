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
    AlertCircle,
    RefreshCw,
    Info,
    CheckCircle,
} from "lucide-react";
import axios from "axios";

const HistoryModal = ({ isOpen, onClose }) => {
    const [historyData, setHistoryData] = useState([]);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);
    const [retryCount, setRetryCount] = useState(0);
    const [debugInfo, setDebugInfo] = useState(null);
    const [showDebug, setShowDebug] = useState(false);

    // Fetch history data when modal opens
    useEffect(() => {
        if (isOpen) {
            fetchHistoryData();
        } else {
            // Reset state when modal closes
            resetModalState();
        }
    }, [isOpen]);

    const resetModalState = () => {
        setHistoryData([]);
        setError(null);
        setDebugInfo(null);
        setShowDebug(false);
        setRetryCount(0);
    };

    const fetchHistoryData = async () => {
        setLoading(true);
        setError(null);
        setDebugInfo(null);

        try {
            console.log("HISTORY MODAL: Fetching employee history...");

            const response = await axios.get(
                "/api/dashboard/employee-history",
                {
                    timeout: 15000, // 15 second timeout
                    headers: {
                        Accept: "application/json",
                        "Content-Type": "application/json",
                    },
                }
            );

            console.log("HISTORY MODAL: API Response received:", response.data);

            // Enhanced response handling
            if (response.data && response.data.success === true) {
                const history = response.data.history || [];
                setHistoryData(history);
                setDebugInfo(response.data.debug || null);
                setError(null);

                console.log("HISTORY MODAL: History data set successfully:", {
                    total_employees: history.length,
                    sample: history.slice(0, 1),
                });
            } else {
                // Handle API error response
                const errorMessage =
                    response.data?.error ||
                    response.data?.message ||
                    "Gagal mengambil data history karyawan";
                setError(errorMessage);
                setHistoryData([]);
                setDebugInfo(response.data?.debug || null);
                console.error(
                    "HISTORY MODAL: API Error Response:",
                    response.data
                );
            }
        } catch (error) {
            console.error("HISTORY MODAL: Error fetching history:", error);

            let errorMessage = "Terjadi kesalahan saat mengambil data history";
            let debugData = null;

            if (error.response) {
                // Server responded with error status
                console.error(
                    "HISTORY MODAL: Response data:",
                    error.response.data
                );
                console.error(
                    "HISTORY MODAL: Response status:",
                    error.response.status
                );

                debugData = {
                    status: error.response.status,
                    statusText: error.response.statusText,
                    data: error.response.data,
                    url: error.config?.url,
                };

                if (error.response.status === 500) {
                    errorMessage = "Terjadi kesalahan server internal";
                } else if (error.response.status === 404) {
                    errorMessage = "API endpoint tidak ditemukan";
                } else if (error.response.status === 403) {
                    errorMessage = "Tidak memiliki akses untuk melihat data";
                } else if (error.response.data?.message) {
                    errorMessage = error.response.data.message;
                } else if (error.response.data?.error) {
                    errorMessage = error.response.data.error;
                }
            } else if (error.request) {
                // Request made but no response received
                console.error("HISTORY MODAL: Request error:", error.request);
                errorMessage = "Tidak dapat terhubung ke server";
                debugData = {
                    type: "network_error",
                    message: "No response received",
                    url: error.config?.url,
                };
            } else {
                // Error in setting up request
                console.error("HISTORY MODAL: Setup error:", error.message);
                errorMessage = "Terjadi kesalahan dalam permintaan data";
                debugData = {
                    type: "setup_error",
                    message: error.message,
                };
            }

            setError(errorMessage);
            setHistoryData([]);
            setDebugInfo(debugData);
        } finally {
            setLoading(false);
        }
    };

    // Retry function
    const handleRetry = () => {
        setRetryCount((prev) => prev + 1);
        fetchHistoryData();
    };

    // Format date untuk display dengan fallback yang lebih baik
    const formatDate = (dateString) => {
        if (!dateString) return "Tanggal tidak tersedia";

        try {
            const date = new Date(dateString);
            if (isNaN(date.getTime())) {
                return "Format tanggal tidak valid";
            }

            return date.toLocaleDateString("id-ID", {
                weekday: "long",
                year: "numeric",
                month: "long",
                day: "numeric",
                hour: "2-digit",
                minute: "2-digit",
            });
        } catch (e) {
            console.error("HISTORY MODAL: Date formatting error:", e);
            return "Error format tanggal";
        }
    };

    // Enhanced employee initials dengan fallback yang lebih baik
    const getEmployeeInitial = (employee) => {
        if (!employee || !employee.nama_lengkap) {
            return "N";
        }

        try {
            // Gunakan initials dari API jika tersedia
            if (employee.initials) {
                return employee.initials;
            }

            // Generate initials dari nama
            const name = employee.nama_lengkap.trim();
            const words = name.split(" ");

            if (words.length === 1) {
                return words[0].charAt(0).toUpperCase();
            }

            return (words[0].charAt(0) + words[1].charAt(0)).toUpperCase();
        } catch (e) {
            console.error("HISTORY MODAL: Initials generation error:", e);
            return "N";
        }
    };

    // Enhanced organizational structure formatting dengan comprehensive fallback
    const formatOrganizationalStructure = (structure) => {
        if (!structure) {
            return "Struktur organisasi tidak tersedia";
        }

        try {
            // Jika structure adalah string, return as is
            if (typeof structure === "string") {
                return structure;
            }

            // Jika structure adalah object, build hierarchy
            if (typeof structure === "object") {
                // Gunakan full_structure jika tersedia dari API
                if (structure.full_structure) {
                    return structure.full_structure;
                }

                // Build structure dari components
                const parts = [];

                if (structure.unit_organisasi) {
                    parts.push(structure.unit_organisasi);
                }

                if (structure.unit) {
                    if (
                        typeof structure.unit === "object" &&
                        structure.unit.name
                    ) {
                        parts.push(structure.unit.name);
                    } else if (typeof structure.unit === "string") {
                        parts.push(structure.unit);
                    }
                }

                if (structure.sub_unit) {
                    if (
                        typeof structure.sub_unit === "object" &&
                        structure.sub_unit.name
                    ) {
                        parts.push(structure.sub_unit.name);
                    } else if (typeof structure.sub_unit === "string") {
                        parts.push(structure.sub_unit);
                    }
                }

                return parts.length > 0
                    ? parts.join(" > ")
                    : "Struktur tidak lengkap";
            }

            return "Format struktur tidak dikenali";
        } catch (e) {
            console.error("HISTORY MODAL: Structure formatting error:", e);
            return "Error format struktur";
        }
    };

    // Enhanced status badge color dengan lebih banyak status
    const getStatusBadgeColor = (status) => {
        if (!status) {
            return "bg-gray-100 text-gray-600 border-gray-300";
        }

        const statusLower = status.toLowerCase();

        if (statusLower.includes("tetap")) {
            return "bg-green-100 text-green-700 border-green-300";
        } else if (statusLower.includes("pkwt")) {
            return "bg-blue-100 text-blue-700 border-blue-300";
        } else if (statusLower.includes("tad")) {
            return "bg-yellow-100 text-yellow-700 border-yellow-300";
        }

        return "bg-gray-100 text-gray-600 border-gray-300";
    };

    // Enhanced position badge color
    const getPositionBadgeColor = (position) => {
        if (!position) {
            return "bg-gray-100 text-gray-600 border-gray-300";
        }

        const positionLower = position.toLowerCase();

        if (positionLower.includes("supervisor")) {
            return "bg-purple-100 text-purple-700 border-purple-300";
        } else if (positionLower.includes("manager")) {
            return "bg-indigo-100 text-indigo-700 border-indigo-300";
        } else if (positionLower.includes("staff")) {
            return "bg-cyan-100 text-cyan-700 border-cyan-300";
        } else if (positionLower.includes("executive")) {
            return "bg-amber-100 text-amber-700 border-amber-300";
        }

        return "bg-gray-100 text-gray-600 border-gray-300";
    };

    // Close modal handler
    const handleClose = () => {
        resetModalState();
        onClose();
    };

    // Toggle debug info
    const toggleDebugInfo = () => {
        setShowDebug(!showDebug);
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
                    <div className="flex items-center gap-2">
                        {/* Debug Toggle Button */}
                        {(debugInfo || error) && (
                            <button
                                onClick={toggleDebugInfo}
                                className="p-2 transition-all duration-200 bg-white rounded-lg bg-opacity-20 hover:bg-opacity-30"
                                title="Toggle Debug Info"
                            >
                                <Info className="w-5 h-5" />
                            </button>
                        )}
                        <button
                            onClick={handleClose}
                            className="p-2 transition-all duration-200 bg-white rounded-lg bg-opacity-20 hover:bg-white hover:bg-opacity-30"
                        >
                            <X className="w-6 h-6" />
                        </button>
                    </div>
                </div>

                {/* Content */}
                <div className="flex-1 overflow-auto max-h-[calc(90vh-80px)]">
                    {loading ? (
                        <div className="flex flex-col items-center justify-center py-16">
                            <RefreshCw className="w-12 h-12 text-[#439454] animate-spin mb-4" />
                            <p className="mb-2 text-lg font-medium text-gray-700">
                                Memuat data history...
                            </p>
                            <p className="text-sm text-gray-500">
                                Mohon tunggu sebentar
                            </p>
                        </div>
                    ) : error ? (
                        <div className="flex flex-col items-center justify-center py-16">
                            <div className="flex items-center justify-center w-16 h-16 mb-4 bg-red-100 rounded-full">
                                <AlertCircle className="w-8 h-8 text-red-500" />
                            </div>
                            <h3 className="mb-2 text-xl font-bold text-red-600">
                                Terjadi Kesalahan
                            </h3>
                            <p className="max-w-md mb-6 text-center text-gray-600">
                                {error}
                            </p>
                            <div className="flex gap-3">
                                <button
                                    onClick={handleRetry}
                                    className="px-6 py-2 bg-[#439454] text-white rounded-lg hover:bg-[#367a41] transition-colors flex items-center gap-2"
                                >
                                    <RefreshCw className="w-4 h-4" />
                                    Coba Lagi
                                </button>
                                <button
                                    onClick={handleClose}
                                    className="px-6 py-2 text-white transition-colors bg-gray-500 rounded-lg hover:bg-gray-600"
                                >
                                    Tutup
                                </button>
                            </div>
                            {retryCount > 0 && (
                                <div className="mt-4 text-sm text-gray-500">
                                    Percobaan ke-{retryCount + 1}
                                </div>
                            )}
                        </div>
                    ) : historyData.length === 0 ? (
                        <div className="flex flex-col items-center justify-center py-16">
                            <div className="flex items-center justify-center w-16 h-16 mb-4 bg-blue-100 rounded-full">
                                <Users className="w-8 h-8 text-blue-500" />
                            </div>
                            <h3 className="mb-2 text-xl font-bold text-gray-700">
                                Tidak Ada Data Baru
                            </h3>
                            <p className="max-w-md text-center text-gray-500">
                                Belum ada karyawan baru yang ditambahkan dalam
                                30 hari terakhir.
                            </p>
                        </div>
                    ) : (
                        <div className="p-6">
                            {/* Summary Header */}
                            <div className="p-4 mb-6 border border-green-200 rounded-lg bg-green-50">
                                <div className="flex items-center gap-2 mb-2">
                                    <CheckCircle className="w-5 h-5 text-green-600" />
                                    <span className="font-medium text-green-800">
                                        Data Berhasil Dimuat
                                    </span>
                                </div>
                                <p className="text-sm text-green-700">
                                    Menampilkan {historyData.length} karyawan
                                    baru dalam 30 hari terakhir
                                </p>
                            </div>

                            {/* Employee Cards */}
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
                                                            {employee.nama_lengkap ||
                                                                "Nama tidak tersedia"}
                                                        </h3>
                                                        <div className="flex items-center gap-2 mb-2">
                                                            {employee.nip && (
                                                                <span className="text-sm text-gray-600">
                                                                    NIP:{" "}
                                                                    {
                                                                        employee.nip
                                                                    }
                                                                </span>
                                                            )}
                                                            {employee.nik && (
                                                                <>
                                                                    {employee.nip && (
                                                                        <span className="text-gray-400">
                                                                            •
                                                                        </span>
                                                                    )}
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

                    {/* Debug Information Panel */}
                    {showDebug && debugInfo && (
                        <div className="p-4 border-t border-gray-200 bg-gray-50">
                            <div className="max-w-full">
                                <h4 className="mb-2 font-medium text-gray-700">
                                    Debug Information:
                                </h4>
                                <div className="p-3 overflow-auto font-mono text-xs text-green-400 bg-gray-800 rounded-lg max-h-40">
                                    <pre>
                                        {JSON.stringify(debugInfo, null, 2)}
                                    </pre>
                                </div>
                                {retryCount > 0 && (
                                    <div className="mt-2 text-xs text-gray-600">
                                        Retry attempts: {retryCount}
                                    </div>
                                )}
                            </div>
                        </div>
                    )}
                </div>

                {/* Footer */}
                <div className="px-6 py-4 border-t border-gray-200 bg-gray-50">
                    <div className="flex items-center justify-between">
                        <p className="text-xs text-gray-500">
                            Data history karyawan yang baru ditambahkan dalam 30
                            hari terakhir
                        </p>
                        {debugInfo && !loading && !error && (
                            <div className="text-xs text-gray-400">
                                Last updated:{" "}
                                {debugInfo.timestamp &&
                                    new Date(
                                        debugInfo.timestamp
                                    ).toLocaleString("id-ID")}
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </div>
    );
};

export default HistoryModal;
