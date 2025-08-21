import React, { useState, useEffect } from "react";
import {
    Clock,
    User,
    Building2,
    Calendar,
    RefreshCw,
    AlertCircle,
} from "lucide-react";

const EmployeeHistory = () => {
    const [historyData, setHistoryData] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [refreshing, setRefreshing] = useState(false);

    useEffect(() => {
        fetchEmployeeHistory();
    }, []);

    const fetchEmployeeHistory = async () => {
        try {
            setLoading(true);
            setError(null);

            const response = await fetch("/api/dashboard/employee-history");

            if (response.ok) {
                const data = await response.json();

                // Check for success field in response
                if (data.success) {
                    setHistoryData(data.history || []);
                } else {
                    setError(data.error || "Gagal memuat data history");
                }
            } else {
                // Handle HTTP error statuses
                const errorText =
                    response.status === 404
                        ? "Endpoint tidak ditemukan"
                        : response.status === 500
                        ? "Terjadi kesalahan server"
                        : `Error: ${response.status}`;
                setError(errorText);
            }
        } catch (error) {
            console.error("Error fetching employee history:", error);
            setError("Tidak dapat terhubung ke server");
        } finally {
            setLoading(false);
        }
    };

    const handleRefresh = async () => {
        setRefreshing(true);
        try {
            await fetchEmployeeHistory();
        } finally {
            setRefreshing(false);
        }
    };

    const formatDate = (dateString) => {
        if (!dateString) return "-";
        try {
            const date = new Date(dateString);
            return date.toLocaleDateString("id-ID", {
                day: "2-digit",
                month: "short",
                year: "numeric",
                hour: "2-digit",
                minute: "2-digit",
            });
        } catch {
            return "-";
        }
    };

    const getOrganizationStructure = (employee) => {
        const parts = [];

        if (employee.unit_organisasi) {
            parts.push(employee.unit_organisasi);
        }

        if (employee.unit_name && employee.unit_name !== "-") {
            parts.push(employee.unit_name);
        }

        if (employee.sub_unit_name && employee.sub_unit_name !== "-") {
            parts.push(employee.sub_unit_name);
        }

        return parts.length > 0
            ? parts.join(" → ")
            : "Struktur organisasi belum lengkap";
    };

    const getTimeAgo = (dateString) => {
        if (!dateString) return "";

        const now = new Date();
        const date = new Date(dateString);
        const diffInMs = now - date;
        const diffInHours = Math.floor(diffInMs / (1000 * 60 * 60));
        const diffInDays = Math.floor(diffInMs / (1000 * 60 * 60 * 24));

        if (diffInHours < 1) {
            const diffInMinutes = Math.floor(diffInMs / (1000 * 60));
            return diffInMinutes <= 1
                ? "Baru saja"
                : `${diffInMinutes} menit yang lalu`;
        } else if (diffInHours < 24) {
            return `${diffInHours} jam yang lalu`;
        } else if (diffInDays === 1) {
            return "Kemarin";
        } else if (diffInDays <= 7) {
            return `${diffInDays} hari yang lalu`;
        } else {
            return formatDate(dateString);
        }
    };

    // Loading state
    if (loading) {
        return (
            <div className="p-6 bg-white border border-gray-100 shadow-sm rounded-2xl">
                <div className="flex items-center justify-between mb-6">
                    <div className="flex items-center gap-3">
                        <div className="flex items-center justify-center w-12 h-12 bg-orange-100 rounded-xl">
                            <Clock className="w-6 h-6 text-orange-600" />
                        </div>
                        <div>
                            <h3 className="text-lg font-semibold text-gray-900">
                                History Karyawan
                            </h3>
                            <p className="text-sm text-gray-500">
                                Karyawan yang baru ditambahkan
                            </p>
                        </div>
                    </div>
                    <div className="flex items-center justify-center w-8 h-8">
                        <RefreshCw className="w-4 h-4 text-gray-400 animate-spin" />
                    </div>
                </div>

                <div className="space-y-4">
                    {[...Array(3)].map((_, index) => (
                        <div
                            key={index}
                            className="flex items-start gap-4 p-4 border border-gray-100 rounded-xl animate-pulse"
                        >
                            <div className="w-10 h-10 bg-gray-200 rounded-lg"></div>
                            <div className="flex-1 space-y-2">
                                <div className="w-3/4 h-4 bg-gray-200 rounded"></div>
                                <div className="w-1/2 h-3 bg-gray-200 rounded"></div>
                                <div className="w-1/4 h-3 bg-gray-200 rounded"></div>
                            </div>
                        </div>
                    ))}
                </div>
            </div>
        );
    }

    // Error state
    if (error) {
        return (
            <div className="p-6 bg-white border border-gray-100 shadow-sm rounded-2xl">
                <div className="flex items-center justify-between mb-6">
                    <div className="flex items-center gap-3">
                        <div className="flex items-center justify-center w-12 h-12 bg-orange-100 rounded-xl">
                            <Clock className="w-6 h-6 text-orange-600" />
                        </div>
                        <div>
                            <h3 className="text-lg font-semibold text-gray-900">
                                History Karyawan
                            </h3>
                            <p className="text-sm text-gray-500">
                                Karyawan yang baru ditambahkan
                            </p>
                        </div>
                    </div>
                    <button
                        onClick={handleRefresh}
                        disabled={refreshing}
                        className="p-2 text-gray-400 transition-colors rounded-lg hover:text-gray-600 hover:bg-gray-100 disabled:opacity-50"
                        title="Refresh data"
                    >
                        <RefreshCw
                            className={`w-4 h-4 ${
                                refreshing ? "animate-spin" : ""
                            }`}
                        />
                    </button>
                </div>

                <div className="py-8 text-center">
                    <div className="flex items-center justify-center w-12 h-12 mx-auto mb-3 bg-red-100 rounded-lg">
                        <AlertCircle className="w-6 h-6 text-red-500" />
                    </div>
                    <div className="mb-3 text-sm text-red-600">{error}</div>
                    <button
                        onClick={handleRefresh}
                        disabled={refreshing}
                        className="px-4 py-2 text-sm text-white transition-colors rounded-lg disabled:opacity-50"
                        style={{ backgroundColor: "#439454" }}
                    >
                        {refreshing ? "Memuat..." : "Coba Lagi"}
                    </button>
                </div>
            </div>
        );
    }

    // Main render
    return (
        <div className="p-6 bg-white border border-gray-100 shadow-sm rounded-2xl">
            <div className="flex items-center justify-between mb-6">
                <div className="flex items-center gap-3">
                    <div className="flex items-center justify-center w-12 h-12 bg-orange-100 rounded-xl">
                        <Clock className="w-6 h-6 text-orange-600" />
                    </div>
                    <div>
                        <h3 className="text-lg font-semibold text-gray-900">
                            History Karyawan
                        </h3>
                        <p className="text-sm text-gray-500">
                            Karyawan yang baru ditambahkan
                        </p>
                    </div>
                </div>

                {/* Refresh button */}
                <button
                    onClick={handleRefresh}
                    disabled={refreshing}
                    className="p-2 text-gray-400 transition-colors rounded-lg hover:text-gray-600 hover:bg-gray-100 disabled:opacity-50"
                    title="Refresh data"
                >
                    <RefreshCw
                        className={`w-4 h-4 ${
                            refreshing ? "animate-spin" : ""
                        }`}
                    />
                </button>
            </div>

            <div className="space-y-4 overflow-y-auto max-h-96">
                {historyData.length === 0 ? (
                    <div className="py-8 text-center">
                        <div className="flex items-center justify-center w-12 h-12 mx-auto mb-3 bg-gray-100 rounded-lg">
                            <Clock className="w-6 h-6 text-gray-400" />
                        </div>
                        <p className="text-sm text-gray-500">
                            Belum ada karyawan yang ditambahkan baru-baru ini
                        </p>
                        <button
                            onClick={handleRefresh}
                            className="mt-2 text-xs text-gray-400 transition-colors hover:text-gray-600"
                        >
                            Klik refresh untuk memuat ulang
                        </button>
                    </div>
                ) : (
                    historyData.map((employee, index) => (
                        <div
                            key={employee.id || index}
                            className="flex items-start gap-4 p-4 transition-all duration-200 border border-gray-100 rounded-xl hover:border-gray-200 hover:bg-gray-50"
                        >
                            <div
                                className="flex items-center justify-center flex-shrink-0 w-10 h-10 rounded-lg"
                                style={{ backgroundColor: "#439454" }}
                            >
                                <User className="w-5 h-5 text-white" />
                            </div>

                            <div className="flex-1 min-w-0">
                                <div className="flex items-start justify-between gap-2">
                                    <h4 className="font-medium text-gray-900 truncate">
                                        {employee.nama_lengkap ||
                                            "Nama tidak tersedia"}
                                    </h4>
                                    <span className="text-xs text-gray-500 whitespace-nowrap">
                                        {getTimeAgo(employee.created_at)}
                                    </span>
                                </div>

                                <div className="flex items-center gap-1 mt-1 text-sm text-gray-600">
                                    <Building2 className="flex-shrink-0 w-4 h-4" />
                                    <span className="truncate">
                                        {getOrganizationStructure(employee)}
                                    </span>
                                </div>

                                <div className="flex items-center gap-1 mt-1 text-xs text-gray-500">
                                    <Calendar className="flex-shrink-0 w-3 h-3" />
                                    <span>
                                        Ditambahkan:{" "}
                                        {formatDate(employee.created_at)}
                                    </span>
                                </div>

                                <div className="flex flex-wrap gap-1 mt-2">
                                    {employee.jabatan && (
                                        <span className="inline-block px-2 py-1 text-xs text-blue-700 bg-blue-100 rounded-md">
                                            {employee.jabatan}
                                        </span>
                                    )}

                                    {employee.status_pegawai && (
                                        <span className="inline-block px-2 py-1 text-xs text-purple-700 bg-purple-100 rounded-md">
                                            {employee.status_pegawai}
                                        </span>
                                    )}
                                </div>
                            </div>
                        </div>
                    ))
                )}
            </div>

            {historyData.length > 0 && (
                <div className="pt-4 mt-4 border-t border-gray-100">
                    <div className="flex items-center justify-between text-xs text-gray-500">
                        <span>
                            Menampilkan {historyData.length} karyawan terbaru
                            dalam 30 hari terakhir
                        </span>
                        <span className="text-gray-400">
                            Terakhir diperbarui:{" "}
                            {new Date().toLocaleTimeString("id-ID", {
                                hour: "2-digit",
                                minute: "2-digit",
                            })}
                        </span>
                    </div>
                </div>
            )}
        </div>
    );
};

export default EmployeeHistory;
