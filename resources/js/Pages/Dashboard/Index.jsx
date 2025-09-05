import React, { useState, useEffect, useCallback } from "react";
import { Head } from "@inertiajs/react";
import DashboardLayout from "../../Layouts/DashboardLayout";

export default function Index({ statistics = {} }) {
    const [stats, setStats] = useState({
        total_employees: 0,
        active_employees: 0,
        pegawai_tetap: 0,
        pkwt: 0,
        tad_total: 0,
        tad_paket_sdm: 0,
        tad_paket_pekerjaan: 0,
        ...statistics,
    });
    const [chartData, setChartData] = useState({
        gender: [],
        status: [],
        unit: [],
        provider: [],
        age: [],
        jabatan: [],
    });
    const [loading, setLoading] = useState(true);
    const [lastUpdate, setLastUpdate] = useState(Date.now());

    const BASE_COLOR = "#439454";

    const fetchStatistics = useCallback(async () => {
        try {
            const response = await fetch("/api/dashboard/statistics");
            if (response.ok) {
                const data = await response.json();
                setStats((prevStats) => ({
                    ...prevStats,
                    ...data,
                }));
            }
        } catch (error) {
            console.error("Error fetching statistics:", error);
        }
    }, []);

    const fetchChartData = useCallback(async () => {
        try {
            const response = await fetch("/api/dashboard/charts");
            if (response.ok) {
                const data = await response.json();
                setChartData(data);
            }
        } catch (error) {
            console.error("Error fetching chart data:", error);
        }
    }, []);

    const fetchAllData = useCallback(async () => {
        setLoading(true);
        try {
            await Promise.all([fetchStatistics(), fetchChartData()]);
            setLastUpdate(Date.now());
        } catch (error) {
            console.error("Error fetching data:", error);
        } finally {
            setLoading(false);
        }
    }, [fetchStatistics, fetchChartData]);

    useEffect(() => {
        fetchAllData();

        // Real-time polling setiap 30 detik
        const interval = setInterval(fetchAllData, 30000);

        return () => clearInterval(interval);
    }, [fetchAllData]);

    // Simple Bar Chart Component
    const SimpleBarChart = ({ data, title, description }) => {
        if (!data || data.length === 0) {
            return (
                <div className="flex items-center justify-center h-80">
                    <p className="text-gray-500">Tidak ada data</p>
                </div>
            );
        }

        const maxValue = Math.max(...data.map((item) => item.value));

        return (
            <div className="h-80">
                <div className="mb-6">
                    <h3 className="mb-2 text-2xl font-bold text-gray-900">
                        {title}
                    </h3>
                    <p className="text-gray-600">{description}</p>
                </div>
                <div className="flex items-end justify-between h-64 gap-2 px-4">
                    {data.map((item, index) => (
                        <div
                            key={index}
                            className="flex flex-col items-center flex-1 max-w-16"
                        >
                            <div className="relative w-full">
                                <div
                                    className="w-full transition-all duration-1000 ease-out rounded-t-lg cursor-pointer hover:opacity-80"
                                    style={{
                                        height: `${
                                            (item.value / maxValue) * 200
                                        }px`,
                                        backgroundColor: BASE_COLOR,
                                        minHeight:
                                            item.value > 0 ? "4px" : "0px",
                                    }}
                                    title={`${item.name}: ${item.value}`}
                                >
                                    <div className="absolute text-sm font-semibold text-gray-700 transition-opacity transform -translate-x-1/2 opacity-0 -top-8 left-1/2 hover:opacity-100">
                                        {item.value}
                                    </div>
                                </div>
                            </div>
                            <p className="max-w-full mt-2 text-xs leading-tight text-center text-gray-600 break-words">
                                {item.name}
                            </p>
                        </div>
                    ))}
                </div>
            </div>
        );
    };

    // Simple Pie Chart Component (using CSS)
    const SimplePieChart = ({ data, title, description }) => {
        if (!data || data.length === 0) {
            return (
                <div className="flex items-center justify-center h-80">
                    <p className="text-gray-500">Tidak ada data</p>
                </div>
            );
        }

        const total = data.reduce((sum, item) => sum + item.value, 0);
        let currentAngle = 0;

        return (
            <div className="h-80">
                <div className="mb-6">
                    <h3 className="mb-2 text-2xl font-bold text-gray-900">
                        {title}
                    </h3>
                    <p className="text-gray-600">{description}</p>
                </div>
                <div className="flex items-center justify-center h-64">
                    <div className="relative">
                        {/* Simple Pie using CSS */}
                        <div className="relative w-40 h-40 overflow-hidden border-8 border-gray-200 rounded-full">
                            {data.map((item, index) => {
                                const percentage =
                                    total > 0 ? (item.value / total) * 100 : 0;
                                const color =
                                    index === 0 ? BASE_COLOR : "#8bc981";

                                return (
                                    <div
                                        key={index}
                                        className="absolute top-0 left-0 w-full h-full"
                                        style={{
                                            background: `conic-gradient(${color} ${currentAngle}deg, ${color} ${
                                                currentAngle + percentage * 3.6
                                            }deg, transparent ${
                                                currentAngle + percentage * 3.6
                                            }deg)`,
                                            transform: `rotate(${currentAngle}deg)`,
                                        }}
                                        title={`${item.name}: ${
                                            item.value
                                        } (${percentage.toFixed(1)}%)`}
                                    ></div>
                                );
                            })}
                        </div>

                        {/* Legend */}
                        <div className="absolute top-0 space-y-3 left-48">
                            {data.map((item, index) => {
                                const percentage =
                                    total > 0 ? (item.value / total) * 100 : 0;
                                const color =
                                    index === 0 ? BASE_COLOR : "#8bc981";

                                return (
                                    <div
                                        key={index}
                                        className="flex items-center"
                                    >
                                        <div
                                            className="w-4 h-4 mr-3 rounded-full"
                                            style={{ backgroundColor: color }}
                                        ></div>
                                        <div className="text-sm">
                                            <span className="font-medium">
                                                {item.name}
                                            </span>
                                            <span className="ml-2 text-gray-500">
                                                {item.value} (
                                                {percentage.toFixed(1)}%)
                                            </span>
                                        </div>
                                    </div>
                                );
                            })}
                        </div>
                    </div>
                </div>
            </div>
        );
    };

    return (
        <DashboardLayout title="Dashboard SDM">
            <Head title="Dashboard SDM GAPURA ANGKASA" />

            <div className="min-h-screen bg-gradient-to-br from-gray-50 via-white to-gray-50">
                {/* Header Section */}
                <div className="relative overflow-hidden bg-white shadow-sm">
                    <div className="absolute inset-0 bg-gradient-to-r from-green-500/5 to-transparent"></div>
                    <div className="relative px-8 py-10">
                        <div className="max-w-4xl">
                            <h1 className="mb-3 text-4xl font-bold text-gray-900">
                                Dashboard SDM GAPURA ANGKASA
                            </h1>
                            <p className="mb-6 text-lg text-gray-600">
                                Sistem Manajemen Sumber Daya Manusia Bandar
                                Udara Ngurah Rai
                            </p>
                            <div className="inline-flex items-center px-4 py-3 border border-orange-200 shadow-sm bg-gradient-to-r from-orange-50 to-orange-100 rounded-xl">
                                <div className="flex items-center justify-center w-8 h-8 mr-3 bg-orange-500 rounded-full">
                                    <svg
                                        className="w-4 h-4 text-white"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                            strokeWidth={2}
                                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                        />
                                    </svg>
                                </div>
                                <span className="font-medium text-orange-800">
                                    Informasi Akses: Anda sebagai Super Admin
                                    memiliki akses penuh ke semua fitur sistem.
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Statistics Cards */}
                <div className="px-8 pb-8 -mt-4">
                    <div className="relative z-10 grid grid-cols-1 gap-8 mb-12 md:grid-cols-2 lg:grid-cols-4">
                        {/* Total Karyawan */}
                        <div className="relative overflow-hidden transition-all duration-700 transform bg-white border border-gray-100 shadow-xl stats-card group rounded-3xl hover:shadow-2xl hover:-translate-y-4 hover:scale-105">
                            <div className="absolute inset-0 transition-all duration-700 opacity-0 bg-gradient-to-br from-blue-500/10 to-blue-600/20 group-hover:opacity-100"></div>
                            <div className="relative p-8">
                                <div className="flex items-center justify-between mb-8">
                                    <div
                                        style={{ backgroundColor: BASE_COLOR }}
                                        className="flex items-center justify-center transition-all duration-700 shadow-2xl w-18 h-18 rounded-3xl group-hover:scale-125 group-hover:rotate-6"
                                    >
                                        <svg
                                            className="text-white transition-all duration-700 w-9 h-9 group-hover:scale-110"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                strokeLinecap="round"
                                                strokeLinejoin="round"
                                                strokeWidth={2}
                                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                                            />
                                        </svg>
                                    </div>
                                </div>
                                <div className="transition-all duration-700">
                                    <p className="mb-3 text-4xl font-bold text-gray-900 transition-all duration-700">
                                        {loading ? (
                                            <div className="w-20 h-12 bg-gray-200 rounded animate-pulse"></div>
                                        ) : (
                                            stats.total_employees
                                        )}
                                    </p>
                                    <p className="mb-2 text-lg font-semibold text-gray-600">
                                        Total Karyawan
                                    </p>
                                    <p
                                        className="inline-block px-3 py-1 text-sm font-medium text-white rounded-full"
                                        style={{ backgroundColor: BASE_COLOR }}
                                    >
                                        Seluruh karyawan
                                    </p>
                                </div>
                            </div>
                        </div>

                        {/* Pegawai Aktif */}
                        <div className="relative overflow-hidden transition-all duration-700 transform bg-white border border-gray-100 shadow-xl stats-card group rounded-3xl hover:shadow-2xl hover:-translate-y-4 hover:scale-105">
                            <div className="absolute inset-0 transition-all duration-700 opacity-0 bg-gradient-to-br from-green-500/10 to-green-600/20 group-hover:opacity-100"></div>
                            <div className="relative p-8">
                                <div className="flex items-center justify-between mb-8">
                                    <div className="flex items-center justify-center transition-all duration-700 shadow-2xl w-18 h-18 bg-gradient-to-br from-green-500 to-green-600 rounded-3xl group-hover:scale-125 group-hover:rotate-6">
                                        <svg
                                            className="text-white transition-all duration-700 w-9 h-9 group-hover:scale-110"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                strokeLinecap="round"
                                                strokeLinejoin="round"
                                                strokeWidth={2}
                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                                            />
                                        </svg>
                                    </div>
                                </div>
                                <div className="transition-all duration-700">
                                    <p className="mb-3 text-4xl font-bold text-gray-900 transition-all duration-700">
                                        {loading ? (
                                            <div className="w-20 h-12 bg-gray-200 rounded animate-pulse"></div>
                                        ) : (
                                            stats.active_employees
                                        )}
                                    </p>
                                    <p className="mb-2 text-lg font-semibold text-gray-600">
                                        Pegawai Aktif
                                    </p>
                                    <p className="inline-block px-3 py-1 text-sm font-medium text-green-600 rounded-full bg-green-50">
                                        Status aktif
                                    </p>
                                </div>
                            </div>
                        </div>

                        {/* Pegawai Tetap */}
                        <div className="relative overflow-hidden transition-all duration-700 transform bg-white border border-gray-100 shadow-xl stats-card group rounded-3xl hover:shadow-2xl hover:-translate-y-4 hover:scale-105">
                            <div className="absolute inset-0 transition-all duration-700 opacity-0 bg-gradient-to-br from-orange-500/10 to-orange-600/20 group-hover:opacity-100"></div>
                            <div className="relative p-8">
                                <div className="flex items-center justify-between mb-8">
                                    <div className="flex items-center justify-center transition-all duration-700 shadow-2xl w-18 h-18 bg-gradient-to-br from-orange-500 to-orange-600 rounded-3xl group-hover:scale-125 group-hover:rotate-6">
                                        <svg
                                            className="text-white transition-all duration-700 w-9 h-9 group-hover:scale-110"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                strokeLinecap="round"
                                                strokeLinejoin="round"
                                                strokeWidth={2}
                                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                                            />
                                        </svg>
                                    </div>
                                </div>
                                <div className="transition-all duration-700">
                                    <p className="mb-3 text-4xl font-bold text-gray-900 transition-all duration-700">
                                        {loading ? (
                                            <div className="w-20 h-12 bg-gray-200 rounded animate-pulse"></div>
                                        ) : (
                                            stats.pegawai_tetap
                                        )}
                                    </p>
                                    <p className="mb-2 text-lg font-semibold text-gray-600">
                                        Pegawai Tetap
                                    </p>
                                    <p className="inline-block px-3 py-1 text-sm font-medium text-orange-600 rounded-full bg-orange-50">
                                        Pegawai tetap
                                    </p>
                                </div>
                            </div>
                        </div>

                        {/* TAD */}
                        <div className="relative overflow-hidden transition-all duration-700 transform bg-white border border-gray-100 shadow-xl stats-card group rounded-3xl hover:shadow-2xl hover:-translate-y-4 hover:scale-105">
                            <div className="absolute inset-0 transition-all duration-700 opacity-0 bg-gradient-to-br from-purple-500/10 to-purple-600/20 group-hover:opacity-100"></div>
                            <div className="relative p-8">
                                <div className="flex items-center justify-between mb-8">
                                    <div className="flex items-center justify-center transition-all duration-700 shadow-2xl w-18 h-18 bg-gradient-to-br from-purple-500 to-purple-600 rounded-3xl group-hover:scale-125 group-hover:rotate-6">
                                        <svg
                                            className="text-white transition-all duration-700 w-9 h-9 group-hover:scale-110"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                strokeLinecap="round"
                                                strokeLinejoin="round"
                                                strokeWidth={2}
                                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                            />
                                        </svg>
                                    </div>
                                </div>
                                <div className="transition-all duration-700">
                                    <p className="mb-3 text-4xl font-bold text-gray-900 transition-all duration-700">
                                        {loading ? (
                                            <div className="w-20 h-12 bg-gray-200 rounded animate-pulse"></div>
                                        ) : (
                                            stats.tad_total
                                        )}
                                    </p>
                                    <p className="mb-2 text-lg font-semibold text-gray-600">
                                        TAD
                                    </p>
                                    <p className="inline-block px-3 py-1 text-sm font-medium text-purple-600 rounded-full bg-purple-50">
                                        Tenaga Alih Daya
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Charts Section */}
                    <div className="space-y-8">
                        {/* Chart Row 1: Gender & Status */}
                        <div className="grid grid-cols-1 gap-8 lg:grid-cols-2">
                            {/* Jenis Kelamin Chart */}
                            <div className="p-6 transition-all duration-500 bg-white border border-gray-100 shadow-xl rounded-3xl hover:shadow-2xl hover:-translate-y-2 animate-fadeInUp">
                                <SimplePieChart
                                    data={chartData.gender}
                                    title="Jenis Kelamin"
                                    description="Distribusi berdasarkan jenis kelamin"
                                />
                            </div>

                            {/* Status Pegawai Chart */}
                            <div
                                className="p-6 transition-all duration-500 bg-white border border-gray-100 shadow-xl rounded-3xl hover:shadow-2xl hover:-translate-y-2 animate-fadeInUp"
                                style={{ animationDelay: "0.1s" }}
                            >
                                <SimpleBarChart
                                    data={chartData.status}
                                    title="Status Pegawai"
                                    description="Distribusi berdasarkan status pegawai"
                                />
                            </div>
                        </div>

                        {/* Chart Row 2: Unit & Provider */}
                        <div className="grid grid-cols-1 gap-8 lg:grid-cols-2">
                            {/* Per Unit Chart */}
                            <div
                                className="p-6 transition-all duration-500 bg-white border border-gray-100 shadow-xl rounded-3xl hover:shadow-2xl hover:-translate-y-2 animate-fadeInUp"
                                style={{ animationDelay: "0.2s" }}
                            >
                                <SimpleBarChart
                                    data={chartData.unit}
                                    title="SDM per Unit"
                                    description="Distribusi berdasarkan unit organisasi"
                                />
                            </div>

                            {/* Per Provider Chart */}
                            <div
                                className="p-6 transition-all duration-500 bg-white border border-gray-100 shadow-xl rounded-3xl hover:shadow-2xl hover:-translate-y-2 animate-fadeInUp"
                                style={{ animationDelay: "0.3s" }}
                            >
                                <SimpleBarChart
                                    data={chartData.provider}
                                    title="SDM per Provider"
                                    description="Distribusi berdasarkan perusahaan provider"
                                />
                            </div>
                        </div>

                        {/* Chart Row 3: Age & Position */}
                        <div className="grid grid-cols-1 gap-8 lg:grid-cols-2">
                            {/* Komposisi Usia Chart */}
                            <div
                                className="p-6 transition-all duration-500 bg-white border border-gray-100 shadow-xl rounded-3xl hover:shadow-2xl hover:-translate-y-2 animate-fadeInUp"
                                style={{ animationDelay: "0.4s" }}
                            >
                                <SimpleBarChart
                                    data={chartData.age}
                                    title="Komposisi Usia SDM"
                                    description="Distribusi berdasarkan kelompok usia"
                                />
                            </div>

                            {/* Kelompok Jabatan Chart */}
                            <div
                                className="p-6 transition-all duration-500 bg-white border border-gray-100 shadow-xl rounded-3xl hover:shadow-2xl hover:-translate-y-2 animate-fadeInUp"
                                style={{ animationDelay: "0.5s" }}
                            >
                                <SimpleBarChart
                                    data={chartData.jabatan}
                                    title="Kelompok Jabatan"
                                    description="Distribusi berdasarkan kelompok jabatan"
                                />
                            </div>
                        </div>
                    </div>

                    {/* Last Update Indicator */}
                    <div className="flex items-center justify-center mt-8">
                        <div className="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-full shadow-sm">
                            <div className="w-2 h-2 mr-2 bg-green-500 rounded-full animate-pulse"></div>
                            <span>
                                Terakhir diperbarui:{" "}
                                {new Date(lastUpdate).toLocaleString("id-ID", {
                                    timeZone: "Asia/Makassar",
                                    year: "numeric",
                                    month: "short",
                                    day: "numeric",
                                    hour: "2-digit",
                                    minute: "2-digit",
                                })}{" "}
                                WITA
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <style jsx>{`
                @keyframes fadeInUp {
                    from {
                        opacity: 0;
                        transform: translateY(30px);
                    }
                    to {
                        opacity: 1;
                        transform: translateY(0);
                    }
                }

                .animate-fadeInUp {
                    animation: fadeInUp 0.8s ease-out forwards;
                }

                .stats-card {
                    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
                }

                .stats-card:hover {
                    transform: translateY(-8px) scale(1.02);
                    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
                }
            `}</style>
        </DashboardLayout>
    );
}
