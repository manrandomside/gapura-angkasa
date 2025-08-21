import React, { useState, useEffect } from "react";
import { Head, Link } from "@inertiajs/react";
import DashboardLayout from "../../Layouts/DashboardLayout";
import EmployeeHistory from "../../Components/EmployeeHistory";

export default function Index({ statistics = {}, success, error, info }) {
    const [stats, setStats] = useState({
        total_employees: 0,
        active_employees: 0,
        pegawai_tetap: 0,
        pkwt: 0,
        tad_total: 0,
        tad_paket_sdm: 0,
        tad_paket_pekerjaan: 0,
        recent_hires: 0,
        ...statistics,
    });
    const [loading, setLoading] = useState(true);
    const [refreshing, setRefreshing] = useState(false);

    useEffect(() => {
        fetchStatistics();
    }, []);

    const fetchStatistics = async () => {
        try {
            setRefreshing(true);
            const response = await fetch("/api/dashboard/statistics");
            if (response.ok) {
                const data = await response.json();
                setStats(data);
            }
        } catch (error) {
            console.error("Error fetching statistics:", error);
        } finally {
            setLoading(false);
            setRefreshing(false);
        }
    };

    const handleRefresh = () => {
        fetchStatistics();
    };

    const formatNumber = (num) => {
        return new Intl.NumberFormat("id-ID").format(num || 0);
    };

    const calculateTADTotal = () => {
        const tadSDM = stats.tad_paket_sdm || 0;
        const tadPekerjaan = stats.tad_paket_pekerjaan || 0;
        return tadSDM + tadPekerjaan;
    };

    const quickActions = [
        {
            title: "Management Karyawan",
            description: "Kelola data karyawan GAPURA ANGKASA",
            icon: (
                <svg
                    className="w-8 h-8"
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
            ),
            gradient: "from-blue-500 to-blue-600",
            href: "/employees",
        },
        {
            title: "Tambah Karyawan",
            description: "Tambah data karyawan baru",
            icon: (
                <svg
                    className="w-8 h-8"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        strokeLinecap="round"
                        strokeLinejoin="round"
                        strokeWidth={2}
                        d="M12 6v6m0 0v6m0-6h6m-6 0H6"
                    />
                </svg>
            ),
            gradient: "from-green-500 to-green-600",
            href: "/employees/create",
        },
        {
            title: "Import Data",
            description: "Import data karyawan dari file",
            icon: (
                <svg
                    className="w-8 h-8"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        strokeLinecap="round"
                        strokeLinejoin="round"
                        strokeWidth={2}
                        d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"
                    />
                </svg>
            ),
            gradient: "from-purple-500 to-purple-600",
            href: "/employees/import",
        },
        {
            title: "Laporan",
            description: "Lihat laporan dan statistik",
            icon: (
                <svg
                    className="w-8 h-8"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        strokeLinecap="round"
                        strokeLinejoin="round"
                        strokeWidth={2}
                        d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                    />
                </svg>
            ),
            gradient: "from-pink-500 to-pink-600",
            href: "/laporan",
        },
    ];

    return (
        <DashboardLayout title="Dashboard SDM">
            <Head title="Dashboard SDM GAPURA ANGKASA" />

            <div className="min-h-screen bg-gradient-to-br from-gray-50 via-white to-gray-50">
                {/* Header Section */}
                <div className="relative overflow-hidden bg-white shadow-sm">
                    <div className="absolute inset-0 bg-gradient-to-r from-green-500/5 to-transparent"></div>
                    <div className="relative px-8 py-10">
                        <div className="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                            <div className="max-w-4xl">
                                <h1 className="mb-3 text-4xl font-bold text-gray-900">
                                    Management Karyawan
                                </h1>
                                <p className="mb-6 text-lg text-gray-600">
                                    Kelola data karyawan PT Gapura Angkasa -
                                    Bandar Udara Ngurah Rai
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
                                        Informasi Akses: Anda sebagai Super
                                        Admin memiliki akses penuh ke semua
                                        fitur sistem.
                                    </span>
                                </div>
                            </div>

                            <div className="flex items-center gap-3 mt-6 lg:mt-0">
                                <button
                                    onClick={handleRefresh}
                                    disabled={refreshing}
                                    className="flex items-center gap-2 px-4 py-2 transition-colors duration-200 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50"
                                >
                                    <svg
                                        className={`w-4 h-4 ${
                                            refreshing ? "animate-spin" : ""
                                        }`}
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                            strokeWidth={2}
                                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                                        />
                                    </svg>
                                    <span className="hidden sm:inline">
                                        Refresh
                                    </span>
                                </button>
                                <Link
                                    href="/employees"
                                    className="flex items-center gap-2 px-4 py-2 text-white transition-colors duration-200 bg-green-600 rounded-lg hover:bg-green-700"
                                >
                                    <svg
                                        className="w-4 h-4"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                            strokeWidth={2}
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                        />
                                        <path
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                            strokeWidth={2}
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                        />
                                    </svg>
                                    <span className="hidden sm:inline">
                                        Lihat Semua
                                    </span>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Notification Messages */}
                {success && (
                    <div className="px-4 py-3 mx-8 mt-6 text-green-700 border border-green-200 rounded-lg bg-green-50">
                        {success}
                    </div>
                )}
                {error && (
                    <div className="px-4 py-3 mx-8 mt-6 text-red-700 border border-red-200 rounded-lg bg-red-50">
                        {error}
                    </div>
                )}
                {info && (
                    <div className="px-4 py-3 mx-8 mt-6 text-blue-700 border border-blue-200 rounded-lg bg-blue-50">
                        {info}
                    </div>
                )}

                <div className="px-8 pb-8 -mt-4">
                    {/* Statistics Cards */}
                    <div className="relative z-10 grid grid-cols-1 gap-8 mb-12 md:grid-cols-2 lg:grid-cols-5">
                        {/* Total Karyawan */}
                        <div className="relative overflow-hidden transition-all duration-700 transform bg-white border border-gray-100 shadow-xl stats-card group rounded-3xl hover:shadow-2xl hover:-translate-y-4 hover:scale-105">
                            <div className="absolute inset-0 transition-all duration-700 opacity-0 bg-gradient-to-br from-blue-500/10 to-blue-600/20 group-hover:opacity-100"></div>
                            <div className="relative p-8">
                                <div className="flex items-center justify-between mb-8">
                                    <div className="flex items-center justify-center transition-all duration-700 shadow-2xl w-18 h-18 bg-gradient-to-br from-blue-500 to-blue-600 rounded-3xl group-hover:scale-125 group-hover:rotate-6 group-hover:shadow-blue-500/50">
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
                                <div className="transition-all duration-700 group-hover:transform group-hover:translate-x-2">
                                    <p className="mb-3 text-4xl font-bold text-gray-900 transition-all duration-700 group-hover:text-blue-600 group-hover:scale-110">
                                        {loading ? (
                                            <div className="w-20 h-12 bg-gray-200 rounded animate-pulse"></div>
                                        ) : (
                                            formatNumber(stats.total_employees)
                                        )}
                                    </p>
                                    <p className="mb-2 text-lg font-semibold text-gray-600 transition-all duration-700 group-hover:text-blue-700">
                                        Total Karyawan
                                    </p>
                                    <p className="inline-block px-3 py-1 text-sm font-medium text-blue-600 transition-all duration-700 rounded-full bg-blue-50 group-hover:bg-blue-600 group-hover:text-white group-hover:scale-105">
                                        Seluruh karyawan
                                    </p>
                                </div>
                            </div>
                        </div>

                        {/* Pegawai Tetap */}
                        <div className="relative overflow-hidden transition-all duration-700 transform bg-white border border-gray-100 shadow-xl stats-card group rounded-3xl hover:shadow-2xl hover:-translate-y-4 hover:scale-105">
                            <div className="absolute inset-0 transition-all duration-700 opacity-0 bg-gradient-to-br from-green-500/10 to-green-600/20 group-hover:opacity-100"></div>
                            <div className="relative p-8">
                                <div className="flex items-center justify-between mb-8">
                                    <div className="flex items-center justify-center transition-all duration-700 shadow-2xl w-18 h-18 bg-gradient-to-br from-green-500 to-green-600 rounded-3xl group-hover:scale-125 group-hover:rotate-6 group-hover:shadow-green-500/50">
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
                                <div className="transition-all duration-700 group-hover:transform group-hover:translate-x-2">
                                    <p className="mb-3 text-4xl font-bold text-gray-900 transition-all duration-700 group-hover:text-green-600 group-hover:scale-110">
                                        {loading ? (
                                            <div className="w-20 h-12 bg-gray-200 rounded animate-pulse"></div>
                                        ) : (
                                            formatNumber(stats.pegawai_tetap)
                                        )}
                                    </p>
                                    <p className="mb-2 text-lg font-semibold text-gray-600 transition-all duration-700 group-hover:text-green-700">
                                        Pegawai Tetap
                                    </p>
                                    <p className="inline-block px-3 py-1 text-sm font-medium text-green-600 transition-all duration-700 rounded-full bg-green-50 group-hover:bg-green-600 group-hover:text-white group-hover:scale-105">
                                        Status tetap
                                    </p>
                                </div>
                            </div>
                        </div>

                        {/* PKWT */}
                        <div className="relative overflow-hidden transition-all duration-700 transform bg-white border border-gray-100 shadow-xl stats-card group rounded-3xl hover:shadow-2xl hover:-translate-y-4 hover:scale-105">
                            <div className="absolute inset-0 transition-all duration-700 opacity-0 bg-gradient-to-br from-purple-500/10 to-purple-600/20 group-hover:opacity-100"></div>
                            <div className="relative p-8">
                                <div className="flex items-center justify-between mb-8">
                                    <div className="flex items-center justify-center transition-all duration-700 shadow-2xl w-18 h-18 bg-gradient-to-br from-purple-500 to-purple-600 rounded-3xl group-hover:scale-125 group-hover:rotate-6 group-hover:shadow-purple-500/50">
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
                                                d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"
                                            />
                                        </svg>
                                    </div>
                                </div>
                                <div className="transition-all duration-700 group-hover:transform group-hover:translate-x-2">
                                    <p className="mb-3 text-4xl font-bold text-gray-900 transition-all duration-700 group-hover:text-purple-600 group-hover:scale-110">
                                        {loading ? (
                                            <div className="w-20 h-12 bg-gray-200 rounded animate-pulse"></div>
                                        ) : (
                                            formatNumber(stats.pkwt)
                                        )}
                                    </p>
                                    <p className="mb-2 text-lg font-semibold text-gray-600 transition-all duration-700 group-hover:text-purple-700">
                                        PKWT
                                    </p>
                                    <p className="inline-block px-3 py-1 text-sm font-medium text-purple-600 transition-all duration-700 rounded-full bg-purple-50 group-hover:bg-purple-600 group-hover:text-white group-hover:scale-105">
                                        Kontrak kerja
                                    </p>
                                </div>
                            </div>
                        </div>

                        {/* TAD */}
                        <div className="relative overflow-hidden transition-all duration-700 transform bg-white border border-gray-100 shadow-xl stats-card group rounded-3xl hover:shadow-2xl hover:-translate-y-4 hover:scale-105">
                            <div className="absolute inset-0 transition-all duration-700 opacity-0 bg-gradient-to-br from-orange-500/10 to-orange-600/20 group-hover:opacity-100"></div>
                            <div className="relative p-8">
                                <div className="flex items-center justify-between mb-8">
                                    <div className="flex items-center justify-center transition-all duration-700 shadow-2xl w-18 h-18 bg-gradient-to-br from-orange-500 to-orange-600 rounded-3xl group-hover:scale-125 group-hover:rotate-6 group-hover:shadow-orange-500/50">
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
                                <div className="transition-all duration-700 group-hover:transform group-hover:translate-x-2">
                                    <p className="mb-3 text-4xl font-bold text-gray-900 transition-all duration-700 group-hover:text-orange-600 group-hover:scale-110">
                                        {loading ? (
                                            <div className="w-20 h-12 bg-gray-200 rounded animate-pulse"></div>
                                        ) : (
                                            formatNumber(calculateTADTotal())
                                        )}
                                    </p>
                                    <p className="mb-2 text-lg font-semibold text-gray-600 transition-all duration-700 group-hover:text-orange-700">
                                        TAD
                                    </p>
                                    <div className="space-y-1 text-xs text-gray-500">
                                        <div>
                                            Paket SDM:{" "}
                                            {formatNumber(stats.tad_paket_sdm)}
                                        </div>
                                        <div>
                                            Paket Pekerjaan:{" "}
                                            {formatNumber(
                                                stats.tad_paket_pekerjaan
                                            )}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {/* Baru Hari Ini */}
                        <div className="relative overflow-hidden transition-all duration-700 transform bg-white border border-gray-100 shadow-xl stats-card group rounded-3xl hover:shadow-2xl hover:-translate-y-4 hover:scale-105">
                            <div className="absolute inset-0 transition-all duration-700 opacity-0 bg-gradient-to-br from-red-500/10 to-red-600/20 group-hover:opacity-100"></div>
                            <div className="relative p-8">
                                <div className="flex items-center justify-between mb-8">
                                    <div className="flex items-center justify-center transition-all duration-700 shadow-2xl w-18 h-18 bg-gradient-to-br from-red-500 to-red-600 rounded-3xl group-hover:scale-125 group-hover:rotate-6 group-hover:shadow-red-500/50">
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
                                                d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"
                                            />
                                        </svg>
                                    </div>
                                </div>
                                <div className="transition-all duration-700 group-hover:transform group-hover:translate-x-2">
                                    <p className="mb-3 text-4xl font-bold text-gray-900 transition-all duration-700 group-hover:text-red-600 group-hover:scale-110">
                                        {loading ? (
                                            <div className="w-20 h-12 bg-gray-200 rounded animate-pulse"></div>
                                        ) : (
                                            formatNumber(
                                                stats.recent_hires || 1
                                            )
                                        )}
                                    </p>
                                    <p className="mb-2 text-lg font-semibold text-gray-600 transition-all duration-700 group-hover:text-red-700">
                                        Baru Hari Ini
                                    </p>
                                    <p className="inline-block px-3 py-1 text-sm font-medium text-red-600 transition-all duration-700 rounded-full bg-red-50 group-hover:bg-red-600 group-hover:text-white group-hover:scale-105">
                                        Karyawan baru
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Main Content Grid */}
                    <div className="grid grid-cols-1 gap-8 mb-10 lg:grid-cols-3">
                        {/* Quick Actions - Takes 2 columns */}
                        <div className="lg:col-span-2">
                            <div className="p-8 bg-white border border-gray-100 shadow-sm rounded-2xl">
                                <div className="mb-8">
                                    <h3 className="mb-2 text-2xl font-bold text-gray-900">
                                        Aksi Cepat
                                    </h3>
                                    <p className="text-gray-600">
                                        Akses cepat ke fitur utama sistem
                                    </p>
                                </div>
                                <div className="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                    {quickActions.map((action, index) => (
                                        <Link
                                            key={index}
                                            href={action.href}
                                            className="relative p-6 overflow-hidden transition-all duration-300 transform border border-gray-100 shadow-sm bg-gradient-to-br from-gray-50 to-white quick-action-card group rounded-2xl hover:shadow-lg hover:-translate-y-1 hover:scale-105"
                                        >
                                            <div
                                                className={`absolute inset-0 bg-gradient-to-br ${action.gradient} opacity-0 group-hover:opacity-100 transition-all duration-300`}
                                            ></div>
                                            <div className="relative">
                                                <div className="flex items-start gap-4">
                                                    <div
                                                        className={`flex items-center justify-center w-12 h-12 bg-gradient-to-br ${action.gradient} rounded-xl shadow-lg group-hover:scale-110 transition-all duration-300`}
                                                    >
                                                        <div className="text-white">
                                                            {action.icon}
                                                        </div>
                                                    </div>
                                                    <div className="flex-1">
                                                        <h4 className="mb-1 font-semibold text-gray-900 transition-all duration-300 group-hover:text-white">
                                                            {action.title}
                                                        </h4>
                                                        <p className="text-sm text-gray-600 transition-all duration-300 group-hover:text-white/90">
                                                            {action.description}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </Link>
                                    ))}
                                </div>
                            </div>
                        </div>

                        {/* Employee History Widget - Takes 1 column */}
                        <div className="lg:col-span-1">
                            <EmployeeHistory />
                        </div>
                    </div>

                    {/* System Status Footer */}
                    <div className="relative overflow-hidden bg-gradient-to-br from-green-600 to-green-800 rounded-3xl shadow-2xl group hover:shadow-green-600/25 transition-all duration-700 hover:scale-[1.02]">
                        <div className="absolute inset-0 transition-all duration-700 bg-black/20 group-hover:bg-black/10"></div>
                        <div className="absolute inset-0 transition-all duration-700 opacity-20 bg-gradient-to-r from-white/10 via-transparent to-white/10 group-hover:opacity-30"></div>

                        <div className="absolute w-3 h-3 rounded-full top-8 left-8 bg-white/30 animate-pulse"></div>
                        <div
                            className="absolute w-2 h-2 rounded-full top-16 right-16 bg-white/20 animate-bounce"
                            style={{ animationDelay: "1s" }}
                        ></div>
                        <div
                            className="absolute bottom-12 left-24 w-1.5 h-1.5 bg-white/40 rounded-full animate-ping"
                            style={{ animationDelay: "2s" }}
                        ></div>
                        <div
                            className="absolute w-4 h-4 rounded-full bottom-8 right-8 bg-white/10 animate-pulse"
                            style={{ animationDelay: "0.5s" }}
                        ></div>

                        <div className="relative p-12 text-center text-white">
                            <div className="transition-all duration-700 group-hover:transform group-hover:scale-105">
                                <h3 className="mb-4 text-3xl font-bold transition-all duration-700 group-hover:scale-110">
                                    Sistem Siap Digunakan
                                </h3>
                                <p className="max-w-4xl mx-auto text-lg leading-relaxed transition-all duration-700 text-white/90 group-hover:text-white">
                                    Sistem manajemen SDM GAPURA ANGKASA siap
                                    membantu Anda mengelola sumber daya manusia
                                    dengan efisien. Gunakan menu navigasi di
                                    sebelah kiri untuk mengakses berbagai fitur
                                    yang tersedia.
                                </p>
                            </div>
                            <div className="flex items-center justify-center mt-8 space-x-8 transition-all duration-700 group-hover:scale-105">
                                <div className="flex items-center transition-all duration-700 hover:scale-110">
                                    <div className="w-3 h-3 mr-3 transition-all duration-700 bg-white rounded-full animate-pulse"></div>
                                    <span className="font-medium transition-all duration-700 text-white/90 group-hover:text-white">
                                        Sistem Online
                                    </span>
                                </div>
                                <div className="flex items-center transition-all duration-700 hover:scale-110">
                                    <div className="w-3 h-3 mr-3 transition-all duration-700 bg-white rounded-full"></div>
                                    <span className="font-medium transition-all duration-700 text-white/90 group-hover:text-white">
                                        Data Tersinkronisasi
                                    </span>
                                </div>
                                <div className="flex items-center transition-all duration-700 hover:scale-110">
                                    <div className="w-3 h-3 mr-3 transition-all duration-700 bg-white rounded-full"></div>
                                    <span className="font-medium transition-all duration-700 text-white/90 group-hover:text-white">
                                        Keamanan Aktif
                                    </span>
                                </div>
                            </div>

                            <div className="mt-8 transition-all duration-700 transform translate-y-4 opacity-0 group-hover:opacity-100 group-hover:translate-y-0">
                                <div className="inline-flex items-center px-6 py-3 font-medium text-white transition-all duration-300 rounded-full cursor-pointer bg-white/20 backdrop-blur-sm hover:bg-white/30">
                                    <svg
                                        className="w-5 h-5 mr-2 animate-spin"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                            strokeWidth={2}
                                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                                        />
                                    </svg>
                                    Sistem Aktif & Terpantau
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </DashboardLayout>
    );
}
