import { Head, Link, usePage } from "@inertiajs/react";
import { useState } from "react";

export default function DashboardLayout({ title, children }) {
    const { url } = usePage();
    const [openDropdowns, setOpenDropdowns] = useState({});

    const toggleDropdown = (index) => {
        setOpenDropdowns((prev) => ({
            ...prev,
            [index]: !prev[index],
        }));
    };

    const menuItems = [
        {
            name: "Dashboard",
            icon: (
                <svg
                    className="w-5 h-5"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        strokeLinecap="round"
                        strokeLinejoin="round"
                        strokeWidth={2}
                        d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2 2z"
                    />
                    <path
                        strokeLinecap="round"
                        strokeLinejoin="round"
                        strokeWidth={2}
                        d="M8 5a2 2 0 012-2h4a2 2 0 012 2v2H8V5z"
                    />
                </svg>
            ),
            href: "/dashboard",
            isActive: url === "/dashboard",
        },
        {
            name: "Data Karyawan",
            icon: (
                <svg
                    className="w-5 h-5"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        strokeLinecap="round"
                        strokeLinejoin="round"
                        strokeWidth={2}
                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"
                    />
                </svg>
            ),
            href: "/data-karyawan",
            isActive: url.startsWith("/data-karyawan"),
            hasSubmenu: true,
            submenu: [
                { name: "Daftar Karyawan", href: "/data-karyawan" },
                { name: "Tambah Karyawan", href: "/data-karyawan/create" },
                { name: "Import Data", href: "/data-karyawan/import" },
            ],
        },
        {
            name: "Organisasi",
            icon: (
                <svg
                    className="w-5 h-5"
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
            ),
            href: "/organisasi",
            isActive: url.startsWith("/organisasi"),
            hasSubmenu: true,
            submenu: [
                { name: "Unit Organisasi", href: "/organisasi" },
                { name: "Struktur Organisasi", href: "/organisasi/struktur" },
                { name: "Manajemen Divisi", href: "/organisasi/divisi" },
            ],
        },
        {
            name: "Laporan",
            icon: (
                <svg
                    className="w-5 h-5"
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
            href: "/laporan",
            isActive: url.startsWith("/laporan"),
            hasSubmenu: true,
            submenu: [
                { name: "Laporan Karyawan", href: "/laporan/karyawan" },
                { name: "Laporan Organisasi", href: "/laporan/organisasi" },
                { name: "Export Data", href: "/laporan/export" },
            ],
        },
        {
            name: "Pengaturan",
            icon: (
                <svg
                    className="w-5 h-5"
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
            href: "/pengaturan",
            isActive: url.startsWith("/pengaturan"),
        },
    ];

    return (
        <>
            <Head title={title} />
            <div className="flex h-screen bg-gray-50">
                {/* Custom CSS Styles */}
                <style
                    dangerouslySetInnerHTML={{
                        __html: `
                        .custom-scrollbar {
                            scrollbar-width: thin;
                            scrollbar-color: #439454 #f1f5f9;
                        }
                        
                        .custom-scrollbar::-webkit-scrollbar {
                            width: 8px;
                        }
                        
                        .custom-scrollbar::-webkit-scrollbar-track {
                            background: #f8fafc;
                            border-radius: 6px;
                            margin: 8px 0;
                        }
                        
                        .custom-scrollbar::-webkit-scrollbar-thumb {
                            background: linear-gradient(180deg, #439454, #358945);
                            border-radius: 6px;
                            transition: all 0.3s ease;
                            box-shadow: 0 2px 4px rgba(67, 148, 84, 0.2);
                        }
                        
                        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
                            background: linear-gradient(180deg, #358945, #2d7a3a);
                            box-shadow: 0 4px 8px rgba(67, 148, 84, 0.4);
                            transform: scaleX(1.2);
                        }
                        
                        .menu-item {
                            position: relative;
                            overflow: hidden;
                        }
                        
                        .menu-item::before {
                            content: '';
                            position: absolute;
                            top: 0;
                            left: -100%;
                            width: 100%;
                            height: 100%;
                            background: linear-gradient(90deg, transparent, rgba(67, 148, 84, 0.1), transparent);
                            transition: left 0.6s ease;
                        }
                        
                        .menu-item:hover::before {
                            left: 100%;
                        }
                        
                        .stats-card {
                            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
                        }
                        
                        .stats-card:hover {
                            transform: translateY(-8px) scale(1.02);
                            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
                        }
                        
                        .quick-action-card::before {
                            content: '';
                            position: absolute;
                            top: -50%;
                            left: -50%;
                            width: 200%;
                            height: 200%;
                            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
                            opacity: 0;
                            transition: opacity 0.5s ease;
                            pointer-events: none;
                        }
                        
                        .quick-action-card:hover::before {
                            opacity: 1;
                        }
                        
                        .page-transition {
                            animation: fadeInUp 0.6s ease-out;
                        }
                        
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
                    `,
                    }}
                />

                {/* Sidebar */}
                <div className="fixed z-10 flex flex-col w-64 h-full bg-white border-r border-gray-200 shadow-2xl">
                    {/* Logo Section - Fixed */}
                    <div className="flex items-center justify-center h-24 bg-white border-b border-gray-200">
                        <img
                            src="/gapuraangkasa.jpg"
                            alt="Gapura Angkasa"
                            className="object-contain w-auto h-25"
                            onError={(e) => {
                                e.target.style.display = "none";
                                e.target.nextElementSibling.style.display =
                                    "flex";
                            }}
                        />
                        <div className="items-center justify-center hidden w-16 h-16 rounded-lg bg-gradient-to-br from-green-400 to-green-600">
                            <span className="text-xl font-bold text-white">
                                GA
                            </span>
                        </div>
                    </div>

                    {/* Menu Section - Scrollable with Custom Scrollbar */}
                    <div className="flex-1 py-6 overflow-y-auto custom-scrollbar">
                        <nav className="px-4 space-y-2">
                            {menuItems.map((item, index) => (
                                <div key={index}>
                                    <div
                                        className={`menu-item flex items-center justify-between px-4 py-4 rounded-xl cursor-pointer transition-all duration-300 ${
                                            item.isActive
                                                ? "bg-[#439454] text-white shadow-lg shadow-green-500/30"
                                                : "text-gray-600 hover:text-[#439454] hover:bg-green-50"
                                        }`}
                                        onClick={() => {
                                            if (item.hasSubmenu) {
                                                toggleDropdown(index);
                                            }
                                        }}
                                    >
                                        <Link
                                            href={item.href}
                                            className="flex items-center flex-1 space-x-3"
                                        >
                                            <div
                                                className={`transition-transform duration-300 ${
                                                    item.isActive
                                                        ? "scale-110"
                                                        : "group-hover:scale-110"
                                                }`}
                                            >
                                                {item.icon}
                                            </div>
                                            <span className="text-sm font-medium">
                                                {item.name}
                                            </span>
                                        </Link>
                                        {item.hasSubmenu && (
                                            <svg
                                                className={`w-4 h-4 transition-transform duration-300 ${
                                                    openDropdowns[index]
                                                        ? "rotate-180"
                                                        : ""
                                                }`}
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    strokeLinecap="round"
                                                    strokeLinejoin="round"
                                                    strokeWidth={2}
                                                    d="M19 9l-7 7-7-7"
                                                />
                                            </svg>
                                        )}
                                    </div>

                                    {/* Submenu */}
                                    {item.hasSubmenu &&
                                        openDropdowns[index] && (
                                            <div className="mt-2 ml-4 space-y-1">
                                                {item.submenu.map(
                                                    (subItem, subIndex) => (
                                                        <Link
                                                            key={subIndex}
                                                            href={subItem.href}
                                                            className={`menu-item block px-4 py-3 text-sm rounded-lg transition-all duration-300 ${
                                                                url ===
                                                                subItem.href
                                                                    ? "bg-[#439454] text-white shadow-md"
                                                                    : "text-gray-500 hover:text-[#439454] hover:bg-green-50 hover:ml-2"
                                                            }`}
                                                        >
                                                            {subItem.name}
                                                        </Link>
                                                    )
                                                )}
                                            </div>
                                        )}
                                </div>
                            ))}
                        </nav>
                    </div>

                    {/* Profile Section - Fixed at Bottom */}
                    <div className="p-4 border-t border-gray-200 bg-gradient-to-r from-gray-50 to-white">
                        <div className="flex items-center justify-between">
                            <div className="flex items-center space-x-3">
                                <div className="flex items-center justify-center w-12 h-12 bg-gradient-to-br from-[#439454] to-[#358945] rounded-full shadow-lg">
                                    <span className="text-sm font-bold text-white">
                                        GD
                                    </span>
                                </div>
                                <div className="flex-1 min-w-0">
                                    <p className="text-sm font-semibold text-gray-800 truncate">
                                        GusDek
                                    </p>
                                    <p className="text-xs text-gray-500 truncate">
                                        Jabatan
                                    </p>
                                </div>
                            </div>
                            <button
                                className="p-2 text-gray-400 transition-all duration-300 hover:text-red-500 hover:bg-red-50 rounded-xl hover:scale-110"
                                title="Logout"
                                onClick={() => {
                                    console.log("Logout clicked");
                                }}
                            >
                                <svg
                                    className="w-5 h-5"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth={2}
                                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                {/* Main Content */}
                <div className="flex-1 ml-64 overflow-y-auto bg-gradient-to-br from-gray-50 to-white">
                    <div className="page-transition">{children}</div>
                </div>
            </div>
        </>
    );
}
