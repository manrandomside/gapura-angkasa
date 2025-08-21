import React, { useState, useEffect } from "react";
import { Clock, User, Building2, Calendar } from "lucide-react";

const EmployeeHistory = () => {
    const [historyData, setHistoryData] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        fetchEmployeeHistory();
    }, []);

    const fetchEmployeeHistory = async () => {
        try {
            setLoading(true);
            const response = await fetch("/api/dashboard/employee-history");
            if (response.ok) {
                const data = await response.json();
                setHistoryData(data.history || []);
            } else {
                setError("Gagal memuat data history");
            }
        } catch (error) {
            console.error("Error fetching employee history:", error);
            setError("Terjadi kesalahan saat memuat data");
        } finally {
            setLoading(false);
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

    if (loading) {
        return (
            <div className="p-6 bg-white border border-gray-100 shadow-sm rounded-2xl">
                <div className="flex items-center gap-3 mb-6">
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

    if (error) {
        return (
            <div className="p-6 bg-white border border-gray-100 shadow-sm rounded-2xl">
                <div className="flex items-center gap-3 mb-6">
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

                <div className="py-8 text-center">
                    <div className="text-sm text-red-500">{error}</div>
                </div>
            </div>
        );
    }

    return (
        <div className="p-6 bg-white border border-gray-100 shadow-sm rounded-2xl">
            <div className="flex items-center gap-3 mb-6">
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

            <div className="space-y-4 overflow-y-auto max-h-96">
                {historyData.length === 0 ? (
                    <div className="py-8 text-center">
                        <Clock className="w-12 h-12 mx-auto mb-3 text-gray-300" />
                        <p className="text-sm text-gray-500">
                            Belum ada karyawan yang ditambahkan baru-baru ini
                        </p>
                    </div>
                ) : (
                    historyData.map((employee, index) => (
                        <div
                            key={employee.id || index}
                            className="flex items-start gap-4 p-4 transition-all duration-200 border border-gray-100 rounded-xl hover:border-gray-200 hover:bg-gray-50"
                        >
                            <div className="flex items-center justify-center flex-shrink-0 w-10 h-10 bg-green-100 rounded-lg">
                                <User className="w-5 h-5 text-green-600" />
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

                                {employee.jabatan && (
                                    <div className="mt-1">
                                        <span className="inline-block px-2 py-1 text-xs text-blue-700 bg-blue-100 rounded-md">
                                            {employee.jabatan}
                                        </span>
                                    </div>
                                )}
                            </div>
                        </div>
                    ))
                )}
            </div>

            {historyData.length > 0 && (
                <div className="pt-4 mt-4 border-t border-gray-100">
                    <div className="text-center">
                        <span className="text-xs text-gray-500">
                            Menampilkan {historyData.length} karyawan terbaru
                            dalam 30 hari terakhir
                        </span>
                    </div>
                </div>
            )}
        </div>
    );
};

export default EmployeeHistory;
