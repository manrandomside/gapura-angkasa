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
    TrendingUp,
    UserPlus,
    Database,
    Wifi,
    WifiOff,
} from "lucide-react";
import axios from "axios";

const HistoryModal = ({ isOpen, onClose }) => {
    const [historyData, setHistoryData] = useState([]);
    const [summaryData, setSummaryData] = useState(null);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);
    const [retryCount, setRetryCount] = useState(0);
    const [debugInfo, setDebugInfo] = useState(null);
    const [showDebug, setShowDebug] = useState(false);
    const [connectionStatus, setConnectionStatus] = useState("checking");

    // Reset modal state
    const resetModalState = () => {
        setHistoryData([]);
        setSummaryData(null);
        setError(null);
        setDebugInfo(null);
        setShowDebug(false);
        setRetryCount(0);
        setConnectionStatus("checking");
    };

    // Fetch history data when modal opens
    useEffect(() => {
        if (isOpen) {
            resetModalState();
            fetchHistoryData();
        }
    }, [isOpen]);

    // FIXED: Enhanced fetch function dengan parallel API calls dan better error handling
    const fetchHistoryData = async () => {
        setLoading(true);
        setError(null);
        setDebugInfo(null);
        setConnectionStatus("connecting");

        try {
            console.log("HISTORY MODAL: Starting to fetch employee history...");

            // Parallel fetch untuk history dan summary data dengan timeout yang lebih panjang untuk device lambat
            const [historyResponse, summaryResponse] = await Promise.all([
                axios.get("/api/dashboard/employee-history", {
                    timeout: 60000, // 60 detik untuk device lambat
                    headers: {
                        Accept: "application/json",
                        "Content-Type": "application/json",
                    },
                }),
                axios
                    .get("/api/dashboard/employee-history-summary", {
                        timeout: 60000, // 60 detik untuk device lambat
                        headers: {
                            Accept: "application/json",
                            "Content-Type": "application/json",
                        },
                    })
                    .catch((err) => {
                        console.warn(
                            "HISTORY MODAL: Summary API failed, continuing without summary:",
                            err.message
                        );
                        return null; // Continue without summary if it fails
                    }),
            ]);

            console.log("HISTORY MODAL: API responses received", {
                historyStatus: historyResponse.status,
                historySuccess: historyResponse.data?.success,
                historyDataCount: historyResponse.data?.history?.length || 0,
                summaryStatus: summaryResponse?.status,
                summarySuccess: summaryResponse?.data?.success,
            });

            setConnectionStatus("connected");

            // Process history response
            if (historyResponse.data && historyResponse.data.success === true) {
                const history = historyResponse.data.history || [];
                setHistoryData(history);
                setDebugInfo(historyResponse.data.debug || null);

                console.log(
                    "HISTORY MODAL: History data processed successfully:",
                    {
                        total_employees: history.length,
                        has_organizational_structure:
                            history.length > 0
                                ? !!history[0].organizational_structure
                                : false,
                        sample_employee:
                            history.length > 0
                                ? {
                                      name: history[0].nama_lengkap,
                                      structure:
                                          history[0].organizational_structure
                                              ?.full_structure,
                                      initials: history[0].initials,
                                  }
                                : null,
                    }
                );
            } else {
                throw new Error(
                    historyResponse.data?.error ||
                        historyResponse.data?.message ||
                        "History API returned unsuccessful response"
                );
            }

            // Process summary response if available
            if (
                summaryResponse &&
                summaryResponse.data &&
                summaryResponse.data.success === true
            ) {
                setSummaryData(summaryResponse.data);
                console.log(
                    "HISTORY MODAL: Summary data processed successfully:",
                    summaryResponse.data.summary
                );
            } else {
                console.warn(
                    "HISTORY MODAL: Summary data not available, continuing without summary"
                );
                setSummaryData(null);
            }

            setError(null);
            setRetryCount(0);
        } catch (error) {
            console.error("HISTORY MODAL: Error fetching history:", error);
            setConnectionStatus("disconnected");

            let errorMessage = "Terjadi kesalahan saat mengambil data history";
            let debugData = {
                timestamp: new Date().toISOString(),
                retry_count: retryCount,
                error_type: "unknown",
            };

            if (error.response) {
                // Server responded with error status
                debugData = {
                    ...debugData,
                    error_type: "server_error",
                    status: error.response.status,
                    statusText: error.response.statusText,
                    data: error.response.data,
                    url: error.config?.url,
                    headers: error.response.headers,
                };

                console.error("HISTORY MODAL: Server error details:", {
                    status: error.response.status,
                    data: error.response.data,
                    url: error.config?.url,
                });

                switch (error.response.status) {
                    case 500:
                        errorMessage =
                            "Terjadi kesalahan server internal. Silakan coba lagi.";
                        break;
                    case 404:
                        errorMessage =
                            "API endpoint tidak ditemukan. Periksa konfigurasi route.";
                        break;
                    case 403:
                        errorMessage =
                            "Tidak memiliki akses untuk melihat data history.";
                        break;
                    case 422:
                        errorMessage = "Data request tidak valid.";
                        break;
                    default:
                        errorMessage =
                            error.response.data?.message ||
                            error.response.data?.error ||
                            `Server error (${error.response.status})`;
                }
            } else if (error.request) {
                // Request made but no response received
                debugData = {
                    ...debugData,
                    error_type: "network_error",
                    message: "No response received from server",
                    url: error.config?.url,
                    timeout: error.code === "ECONNABORTED",
                };

                if (error.code === "ECONNABORTED") {
                    errorMessage =
                        "Timeout - Server membutuhkan waktu terlalu lama untuk merespons";
                } else {
                    errorMessage =
                        "Tidak dapat terhubung ke server. Periksa koneksi internet.";
                }
            } else {
                // Error in setting up request
                debugData = {
                    ...debugData,
                    error_type: "setup_error",
                    message: error.message,
                };
                errorMessage = "Terjadi kesalahan dalam permintaan data";
            }

            setError(errorMessage);
            setHistoryData([]);
            setSummaryData(null);
            setDebugInfo(debugData);
        } finally {
            setLoading(false);
        }
    };

    // ENHANCED: Retry function with longer delays for slow devices
    const handleRetry = () => {
        const newRetryCount = retryCount + 1;
        setRetryCount(newRetryCount);

        // Longer delay for slow devices - be more patient
        const delay = Math.min(2000 * newRetryCount, 8000); // Max 8 second delay, increased for patience
        setTimeout(() => {
            fetchHistoryData();
        }, delay);
    };

    // ENHANCED: Date formatting dengan comprehensive fallback
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
            return dateString; // Return original if formatting fails
        }
    };

    // ENHANCED: Employee initials dengan multiple fallback strategies
    const getEmployeeInitial = (employee) => {
        if (!employee) return "N";

        try {
            // Priority 1: Use initials from API if available
            if (employee.initials && typeof employee.initials === "string") {
                return employee.initials.toUpperCase();
            }

            // Priority 2: Generate from nama_lengkap
            if (
                employee.nama_lengkap &&
                typeof employee.nama_lengkap === "string"
            ) {
                const name = employee.nama_lengkap.trim();
                const words = name
                    .split(/\s+/)
                    .filter((word) => word.length > 0);

                if (words.length === 0) return "N";
                if (words.length === 1) return words[0].charAt(0).toUpperCase();

                return (words[0].charAt(0) + words[1].charAt(0)).toUpperCase();
            }

            // Priority 3: Use NIP if available
            if (employee.nip) {
                return employee.nip.charAt(0).toUpperCase();
            }

            // Priority 4: Use NIK if available
            if (employee.nik) {
                return employee.nik.charAt(0);
            }

            return "N";
        } catch (e) {
            console.error("HISTORY MODAL: Initials generation error:", e);
            return "N";
        }
    };

    // ENHANCED: Organizational structure formatting yang kompatibel dengan backend accessor
    const formatOrganizationalStructure = (structure) => {
        if (!structure) {
            return "Struktur organisasi tidak tersedia";
        }

        try {
            // Priority 1: Use full_structure dari backend accessor
            if (typeof structure === "object" && structure.full_structure) {
                return structure.full_structure;
            }

            // Priority 2: Handle string format
            if (typeof structure === "string") {
                return structure;
            }

            // Priority 3: Build from object components
            if (typeof structure === "object") {
                const parts = [];

                // Add unit_organisasi
                if (
                    structure.unit_organisasi &&
                    structure.unit_organisasi !== "Tidak tersedia"
                ) {
                    parts.push(structure.unit_organisasi);
                }

                // Add unit name
                if (structure.unit) {
                    if (
                        typeof structure.unit === "object" &&
                        structure.unit.name
                    ) {
                        parts.push(structure.unit.name);
                    } else if (
                        typeof structure.unit === "string" &&
                        structure.unit !== "Tidak tersedia"
                    ) {
                        parts.push(structure.unit);
                    }
                }

                // Add sub_unit name
                if (structure.sub_unit) {
                    if (
                        typeof structure.sub_unit === "object" &&
                        structure.sub_unit.name
                    ) {
                        parts.push(structure.sub_unit.name);
                    } else if (
                        typeof structure.sub_unit === "string" &&
                        structure.sub_unit !== "Tidak tersedia"
                    ) {
                        parts.push(structure.sub_unit);
                    }
                }

                if (parts.length > 0) {
                    return parts.join(" > ");
                }
            }

            return "Struktur organisasi tidak lengkap";
        } catch (e) {
            console.error("HISTORY MODAL: Structure formatting error:", e);
            return "Error format struktur";
        }
    };

    // UPDATED: Status badge colors - SYNCHRONIZED with Index.jsx
    const getStatusBadgeColor = (status) => {
        if (!status || status === "Tidak tersedia") {
            return "bg-gray-100 text-gray-800 border-gray-300";
        }

        const statusUpper = status.toUpperCase();

        if (
            statusUpper.includes("PEGAWAI TETAP") ||
            statusUpper.includes("TETAP")
        ) {
            return "bg-green-100 text-green-800 border-green-300";
        } else if (statusUpper.includes("PKWT")) {
            return "bg-blue-100 text-blue-800 border-blue-300";
        } else if (statusUpper.includes("TAD PAKET SDM")) {
            return "bg-yellow-100 text-yellow-800 border-yellow-300";
        } else if (statusUpper.includes("TAD PAKET PEKERJAAN")) {
            return "bg-orange-100 text-orange-800 border-orange-300";
        } else if (statusUpper.includes("TAD")) {
            return "bg-yellow-100 text-yellow-800 border-yellow-300";
        }

        return "bg-gray-100 text-gray-800 border-gray-300";
    };

    // UPDATED: Position badge colors - SYNCHRONIZED with Index.jsx including GENERAL MANAGER and NON
    const getPositionBadgeColor = (position) => {
        if (!position || position === "Tidak tersedia") {
            return "bg-gray-100 text-gray-800 border-gray-300";
        }

        const positionUpper = position.toUpperCase();

        // UPDATED: Check for GENERAL MANAGER first before MANAGER (order matters!)
        if (positionUpper.includes("GENERAL MANAGER")) {
            return "bg-teal-100 text-teal-800 border-teal-300";
        } else if (positionUpper.includes("EXECUTIVE GENERAL MANAGER")) {
            return "bg-red-100 text-red-800 border-red-300";
        } else if (
            positionUpper.includes("ACCOUNT EXECUTIVE") ||
            positionUpper.includes("AE")
        ) {
            return "bg-green-100 text-green-800 border-green-300";
        } else if (positionUpper.includes("MANAGER")) {
            return "bg-purple-100 text-purple-800 border-purple-300";
        } else if (positionUpper.includes("SUPERVISOR")) {
            return "bg-indigo-100 text-indigo-800 border-indigo-300";
        } else if (positionUpper.includes("STAFF")) {
            return "bg-blue-100 text-blue-800 border-blue-300";
        } else if (positionUpper === "NON") {
            // UPDATED: Added NON kelompok jabatan - same as default gray
            return "bg-gray-100 text-gray-800 border-gray-300";
        }

        return "bg-gray-100 text-gray-800 border-gray-300";
    };

    // Close modal handler
    const handleClose = () => {
        resetModalState();
        onClose();
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
                        {/* Retry Button */}
                        {!loading && (
                            <button
                                onClick={handleRetry}
                                className="p-2 transition-all duration-200 bg-white rounded-lg bg-opacity-20 hover:bg-opacity-30"
                                title="Refresh Data"
                            >
                                <RefreshCw className="w-5 h-5" />
                            </button>
                        )}

                        {/* Close Button */}
                        <button
                            onClick={handleClose}
                            className="p-2 transition-all duration-200 bg-white rounded-lg bg-opacity-20 hover:bg-white hover:bg-opacity-30"
                        >
                            <X className="w-6 h-6" />
                        </button>
                    </div>
                </div>

                {/* Summary Header - 3 Columns Only */}
                {summaryData && summaryData.summary && !loading && !error && (
                    <div className="p-6 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-green-50">
                        <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
                            <div className="text-center">
                                <div className="text-2xl font-bold text-[#439454]">
                                    {summaryData.summary.today || 0}
                                </div>
                                <div className="text-sm text-gray-600">
                                    Hari Ini
                                </div>
                            </div>
                            <div className="text-center">
                                <div className="text-2xl font-bold text-purple-600">
                                    {summaryData.summary.this_week || 0}
                                </div>
                                <div className="text-sm text-gray-600">
                                    Minggu Ini
                                </div>
                            </div>
                            <div className="text-center">
                                <div className="text-2xl font-bold text-red-600">
                                    {summaryData.summary.total_period || 0}
                                </div>
                                <div className="text-sm text-gray-600">
                                    30 Hari
                                </div>
                                {summaryData.summary.growth_percentage !==
                                    undefined && (
                                    <div
                                        className={`text-xs flex items-center justify-center gap-1 mt-1 ${
                                            summaryData.summary
                                                .growth_percentage >= 0
                                                ? "text-green-600"
                                                : "text-red-600"
                                        }`}
                                    >
                                        <TrendingUp className="w-3 h-3" />
                                        {summaryData.summary.growth_percentage}%
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>
                )}

                {/* Content */}
                <div className="flex-1 overflow-auto max-h-[calc(90vh-140px)]">
                    {loading ? (
                        <div className="flex flex-col items-center justify-center py-16">
                            <RefreshCw className="w-12 h-12 text-[#439454] animate-spin mb-4" />
                            <p className="mb-2 text-lg font-medium text-gray-700">
                                Memuat data history...
                            </p>
                            <p className="text-sm text-gray-500">
                                {retryCount > 0
                                    ? "Sistem sedang berusaha keras untuk device Anda"
                                    : "Mohon tunggu sebentar"}
                            </p>
                            {retryCount > 0 && (
                                <div className="mt-2 text-xs text-gray-400">
                                    Percobaan ke-{retryCount + 1} - Sistem akan
                                    sabar menunggu
                                </div>
                            )}
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
                                    disabled={loading}
                                >
                                    <RefreshCw
                                        className={`w-4 h-4 ${
                                            loading ? "animate-spin" : ""
                                        }`}
                                    />
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
                                <UserPlus className="w-8 h-8 text-blue-500" />
                            </div>
                            <h3 className="mb-2 text-xl font-bold text-gray-700">
                                Tidak Ada Data Baru
                            </h3>
                            <p className="max-w-md text-center text-gray-500">
                                Belum ada karyawan baru yang ditambahkan dalam
                                30 hari terakhir.
                            </p>
                            <button
                                onClick={handleRetry}
                                className="mt-4 px-4 py-2 bg-[#439454] text-white rounded-lg hover:bg-[#367a41] transition-colors flex items-center gap-2 text-sm"
                            >
                                <RefreshCw className="w-4 h-4" />
                                Refresh Data
                            </button>
                        </div>
                    ) : (
                        <div className="p-6">
                            {/* Success Header */}
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
                                                <div className="w-16 h-16 bg-gradient-to-r from-[#439454] to-[#367a41] rounded-full flex items-center justify-center text-white font-bold text-lg shadow-lg">
                                                    {getEmployeeInitial(
                                                        employee
                                                    )}
                                                </div>
                                            </div>

                                            {/* Employee Info */}
                                            <div className="flex-1 min-w-0">
                                                <div className="flex items-start justify-between mb-3">
                                                    <div className="flex-1 min-w-0">
                                                        <h3 className="mb-1 text-xl font-bold text-gray-900 truncate">
                                                            {employee.nama_lengkap ||
                                                                "Nama tidak tersedia"}
                                                        </h3>
                                                        <div className="flex flex-wrap items-center gap-2 mb-2">
                                                            {employee.nip &&
                                                                employee.nip !==
                                                                    "Tidak tersedia" && (
                                                                    <span className="px-2 py-1 text-sm text-gray-600 bg-gray-100 rounded">
                                                                        NIP:{" "}
                                                                        {
                                                                            employee.nip
                                                                        }
                                                                    </span>
                                                                )}
                                                            {employee.nik &&
                                                                employee.nik !==
                                                                    "Tidak tersedia" && (
                                                                    <span className="px-2 py-1 text-sm text-gray-600 bg-gray-100 rounded">
                                                                        NIK:{" "}
                                                                        {
                                                                            employee.nik
                                                                        }
                                                                    </span>
                                                                )}
                                                        </div>
                                                    </div>

                                                    {/* Date Badge */}
                                                    <div className="flex flex-col items-end ml-4">
                                                        <div className="px-3 py-1 bg-[#439454] bg-opacity-10 text-[#439454] rounded-full text-sm font-medium mb-1 whitespace-nowrap">
                                                            {employee.relative_date ||
                                                                formatDate(
                                                                    employee.created_at
                                                                )}
                                                        </div>
                                                        <div className="text-xs text-gray-500 whitespace-nowrap">
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
                                                        <p className="text-sm text-gray-600 bg-gray-50 p-2 rounded border-l-4 border-[#439454]">
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
                                                        <p className="mb-2 text-sm font-medium text-gray-900">
                                                            {employee.jabatan ||
                                                                "Jabatan tidak tersedia"}
                                                        </p>
                                                        {employee.kelompok_jabatan &&
                                                            employee.kelompok_jabatan !==
                                                                "Tidak tersedia" && (
                                                                <span
                                                                    className={`inline-block px-3 py-1 rounded-full text-xs border font-medium ${getPositionBadgeColor(
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
                                                        className={`px-3 py-1 rounded-full text-xs border font-medium ${getStatusBadgeColor(
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
                    <div className="flex items-center justify-between">
                        <div className="flex items-center gap-4">
                            <p className="text-xs text-gray-500">
                                Data history karyawan yang baru ditambahkan
                                dalam 30 hari terakhir
                            </p>
                            {historyData.length > 0 && (
                                <span className="px-2 py-1 text-xs text-green-600 bg-green-100 rounded">
                                    {historyData.length} data ditemukan
                                </span>
                            )}
                        </div>
                        <div className="flex items-center gap-4 text-xs text-gray-400">
                            {debugInfo &&
                                !loading &&
                                !error &&
                                debugInfo.timestamp && (
                                    <span>
                                        Last updated:{" "}
                                        {new Date(
                                            debugInfo.timestamp
                                        ).toLocaleTimeString("id-ID")}
                                    </span>
                                )}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default HistoryModal;
