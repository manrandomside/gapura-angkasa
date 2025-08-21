import React, { useState, useEffect } from "react";
import { Head } from "@inertiajs/react";
import DashboardLayout from "../../Layouts/DashboardLayout";

export default function Index({ statistics = {} }) {
    const [stats, setStats] = useState({
        total_employees: 0,
        active_employees: 0,
        pegawai_tetap: 0,
        tad: 0,
        ...statistics,
    });
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        fetchStatistics();
    }, []);

    const fetchStatistics = async () => {
        try {
            const response = await fetch("/api/dashboard/statistics");
            if (response.ok) {
                const data = await response.json();
                setStats(data);
            }
        } catch (error) {
            console.error("Error fetching statistics:", error);
        } finally {
            setLoading(false);
        }
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
        {
            title: "Pengaturan",
            description: "Kelola pengaturan sistem",
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
                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"
                    />
                    <path
                        strokeLinecap="round"
                        strokeLinejoin="round"
                        strokeWidth={2}
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                    />
                </svg>
            ),
            gradient: "from-indigo-500 to-indigo-600",
            href: "/pengaturan",
        },
    ];

    return (
        <DashboardLayout title="Dashboard SDM">
            <Head title="Dashboard SDM GAPURA ANGKASA" />

            <div className="min-h-screen bg-gradient-to-br from-gray-50 via-white to-gray-50">
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

                <div className="px-8 pb-8 -mt-4">
                    <div className="relative z-10 grid grid-cols-1 gap-8 mb-12 md:grid-cols-2 lg:grid-cols-4">
                        <div className="relative overflow-hidden transition-all duration-700 transform bg-white border border-gray-100 shadow-xl stats-card group rounded-3xl hover:shadow-2xl hover:-translate-y-4 hover:scale-105">
                            <div className="absolute inset-0 transition-all duration-700 opacity-0 bg-gradient-to-br from-blue-500/10 to-blue-600/20 group-hover:opacity-100"></div>
                            <div className="absolute inset-0 transition-opacity duration-700 opacity-0 bg-gradient-to-r from-blue-400/20 via-transparent to-blue-600/20 group-hover:opacity-100 animate-pulse"></div>
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
                                            stats.total_employees
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

                        <div className="relative overflow-hidden transition-all duration-700 transform bg-white border border-gray-100 shadow-xl stats-card group rounded-3xl hover:shadow-2xl hover:-translate-y-4 hover:scale-105">
                            <div className="absolute inset-0 transition-all duration-700 opacity-0 bg-gradient-to-br from-green-500/10 to-green-600/20 group-hover:opacity-100"></div>
                            <div className="absolute inset-0 transition-opacity duration-700 opacity-0 bg-gradient-to-r from-green-400/20 via-transparent to-green-600/20 group-hover:opacity-100 animate-pulse"></div>
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
                                            stats.active_employees
                                        )}
                                    </p>
                                    <p className="mb-2 text-lg font-semibold text-gray-600 transition-all duration-700 group-hover:text-green-700">
                                        Pegawai Aktif
                                    </p>
                                    <p className="inline-block px-3 py-1 text-sm font-medium text-green-600 transition-all duration-700 rounded-full bg-green-50 group-hover:bg-green-600 group-hover:text-white group-hover:scale-105">
                                        Status aktif
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div className="relative overflow-hidden transition-all duration-700 transform bg-white border border-gray-100 shadow-xl stats-card group rounded-3xl hover:shadow-2xl hover:-translate-y-4 hover:scale-105">
                            <div className="absolute inset-0 transition-all duration-700 opacity-0 bg-gradient-to-br from-orange-500/10 to-orange-600/20 group-hover:opacity-100"></div>
                            <div className="absolute inset-0 transition-opacity duration-700 opacity-0 bg-gradient-to-r from-orange-400/20 via-transparent to-orange-600/20 group-hover:opacity-100 animate-pulse"></div>
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
                                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                                            />
                                        </svg>
                                    </div>
                                </div>
                                <div className="transition-all duration-700 group-hover:transform group-hover:translate-x-2">
                                    <p className="mb-3 text-4xl font-bold text-gray-900 transition-all duration-700 group-hover:text-orange-600 group-hover:scale-110">
                                        {loading ? (
                                            <div className="w-20 h-12 bg-gray-200 rounded animate-pulse"></div>
                                        ) : (
                                            stats.pegawai_tetap
                                        )}
                                    </p>
                                    <p className="mb-2 text-lg font-semibold text-gray-600 transition-all duration-700 group-hover:text-orange-700">
                                        Pegawai Tetap
                                    </p>
                                    <p className="inline-block px-3 py-1 text-sm font-medium text-orange-600 transition-all duration-700 rounded-full bg-orange-50 group-hover:bg-orange-600 group-hover:text-white group-hover:scale-105">
                                        Pegawai tetap
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div className="relative overflow-hidden transition-all duration-700 transform bg-white border border-gray-100 shadow-xl stats-card group rounded-3xl hover:shadow-2xl hover:-translate-y-4 hover:scale-105">
                            <div className="absolute inset-0 transition-all duration-700 opacity-0 bg-gradient-to-br from-purple-500/10 to-purple-600/20 group-hover:opacity-100"></div>
                            <div className="absolute inset-0 transition-opacity duration-700 opacity-0 bg-gradient-to-r from-purple-400/20 via-transparent to-purple-600/20 group-hover:opacity-100 animate-pulse"></div>
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
                                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                            />
                                        </svg>
                                    </div>
                                </div>
                                <div className="transition-all duration-700 group-hover:transform group-hover:translate-x-2">
                                    <p className="mb-3 text-4xl font-bold text-gray-900 transition-all duration-700 group-hover:text-purple-600 group-hover:scale-110">
                                        {loading ? (
                                            <div className="w-20 h-12 bg-gray-200 rounded animate-pulse"></div>
                                        ) : (
                                            stats.tad
                                        )}
                                    </p>
                                    <p className="mb-2 text-lg font-semibold text-gray-600 transition-all duration-700 group-hover:text-purple-700">
                                        TAD
                                    </p>
                                    <p className="inline-block px-3 py-1 text-sm font-medium text-purple-600 transition-all duration-700 rounded-full bg-purple-50 group-hover:bg-purple-600 group-hover:text-white group-hover:scale-105">
                                        Tenaga Alih Daya
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div className="mb-10">
                        <div className="mb-8 text-center">
                            <h2 className="mb-3 text-3xl font-bold text-gray-900">
                                Aksi Cepat
                            </h2>
                            <p className="text-lg text-gray-600">
                                Akses cepat ke fitur utama sistem
                            </p>
                        </div>
                        <div className="grid grid-cols-1 gap-8 mx-auto md:grid-cols-2 lg:grid-cols-4 max-w-7xl">
                            {quickActions.map((action, index) => (
                                <a
                                    key={index}
                                    href={action.href}
                                    className="relative overflow-hidden transition-all duration-700 transform bg-white border border-gray-100 shadow-xl quick-action-card group rounded-3xl hover:shadow-2xl hover:-translate-y-6 hover:scale-105"
                                >
                                    <div
                                        className={`absolute inset-0 bg-gradient-to-br ${action.gradient} opacity-0 group-hover:opacity-100 transition-all duration-700`}
                                    ></div>
                                    <div className="absolute inset-0 transition-all duration-700 opacity-0 bg-gradient-to-r from-white/20 via-transparent to-white/20 group-hover:opacity-100 group-hover:animate-pulse"></div>
                                    <div className="relative p-10">
                                        <div className="flex flex-col items-center text-center">
                                            <div
                                                className={`flex items-center justify-center w-24 h-24 bg-gradient-to-br ${action.gradient} rounded-3xl shadow-2xl mb-8 group-hover:scale-125 group-hover:rotate-12 transition-all duration-700 group-hover:shadow-xl`}
                                            >
                                                <div className="text-white transition-all duration-700 group-hover:scale-110">
                                                    {action.icon}
                                                </div>
                                            </div>
                                            <h3 className="mb-3 text-2xl font-bold text-gray-900 transition-all duration-700 group-hover:text-white group-hover:scale-105 group-hover:transform group-hover:-translate-y-1">
                                                {action.title}
                                            </h3>
                                            <p className="text-lg leading-relaxed text-gray-600 transition-all duration-700 group-hover:text-white/90 group-hover:transform group-hover:translate-y-1">
                                                {action.description}
                                            </p>
                                            <div className="mt-6 transition-all duration-700 transform translate-y-4 opacity-0 group-hover:opacity-100 group-hover:translate-y-0 group-hover:scale-110">
                                                <div className="flex items-center px-4 py-2 rounded-full text-white/90 bg-white/20 backdrop-blur-sm">
                                                    <svg
                                                        className="w-4 h-4 mr-2 transition-transform duration-700 group-hover:translate-x-1"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        viewBox="0 0 24 24"
                                                    >
                                                        <path
                                                            strokeLinecap="round"
                                                            strokeLinejoin="round"
                                                            strokeWidth={2}
                                                            d="M13 7l5 5m0 0l-5 5m5-5H6"
                                                        />
                                                    </svg>
                                                    <span className="font-medium">
                                                        Akses sekarang
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div className="absolute w-2 h-2 transition-all duration-700 rounded-full opacity-0 top-4 right-4 bg-white/30 group-hover:opacity-100 group-hover:animate-bounce"></div>
                                    <div
                                        className="absolute w-1 h-1 transition-all duration-700 rounded-full opacity-0 bottom-4 left-4 bg-white/40 group-hover:opacity-100 group-hover:animate-ping"
                                        style={{ animationDelay: "0.5s" }}
                                    ></div>
                                    <div
                                        className="absolute top-1/2 left-8 w-1.5 h-1.5 bg-white/20 rounded-full opacity-0 group-hover:opacity-100 transition-all duration-700 group-hover:animate-pulse"
                                        style={{ animationDelay: "1s" }}
                                    ></div>
                                </a>
                            ))}
                        </div>
                    </div>

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
