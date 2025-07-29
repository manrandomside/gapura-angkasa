import { useState } from "react";
import { Head, Link, usePage } from "@inertiajs/react";

const DashboardLayout = ({ children, title = "Dashboard" }) => {
    const { url } = usePage();
    const [activeSubmenu, setActiveSubmenu] = useState(null);

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
                        d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"
                    />
                    <path
                        strokeLinecap="round"
                        strokeLinejoin="round"
                        strokeWidth={2}
                        d="M8 5a2 2 0 012-2h4a2 2 0 012 2v14l-5-3-5 3V5z"
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
            isActive: url === "/data-karyawan",
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
            isActive: url === "/organisasi",
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
            isActive: url === "/laporan",
            submenu: [
                { name: "Laporan Karyawan", href: "/laporan/karyawan" },
                { name: "Laporan Kehadiran", href: "/laporan/kehadiran" },
                { name: "Laporan Performa", href: "/laporan/performa" },
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
            isActive: url === "/pengaturan",
            submenu: [
                { name: "Pengaturan Sistem", href: "/pengaturan" },
                { name: "Manajemen User", href: "/pengaturan/users" },
                { name: "Backup Data", href: "/pengaturan/backup" },
            ],
        },
    ];

    const toggleSubmenu = (index) => {
        setActiveSubmenu(activeSubmenu === index ? null : index);
    };

    return (
        <>
            <Head title={title} />
            <div className="flex h-screen bg-gray-50">
                {/* Sidebar */}
                <div className="fixed z-10 flex flex-col w-64 h-full bg-white shadow-lg">
                    {/* Logo Section - Fixed */}
                    <div className="flex items-center justify-center h-20 bg-white border-b border-gray-200">
                        <div className="flex items-center space-x-3">
                            <div className="flex items-center justify-center w-10 h-10 rounded-lg bg-gradient-to-br from-green-400 to-green-600">
                                <img
                                    src="/gapuraangkasa.jpg"
                                    alt="Gapura Angkasa"
                                    className="object-contain w-8 h-8"
                                    onError={(e) => {
                                        e.target.style.display = "none";
                                        e.target.nextElementSibling.style.display =
                                            "block";
                                    }}
                                />
                                <span className="hidden text-sm font-bold text-white">
                                    GA
                                </span>
                            </div>
                            <div>
                                <h1 className="text-lg font-bold text-gray-800">
                                    Gapura
                                </h1>
                                <p className="text-xs text-gray-500">
                                    AIRPORT SERVICES
                                </p>
                            </div>
                        </div>
                    </div>

                    {/* Menu Section - Scrollable */}
                    <div className="flex-1 py-4 overflow-y-auto">
                        <nav className="px-4 space-y-2">
                            {menuItems.map((item, index) => (
                                <div key={index}>
                                    <div
                                        className={`flex items-center justify-between px-4 py-3 rounded-lg cursor-pointer transition-all duration-200 ${
                                            item.isActive
                                                ? "bg-[#439454] text-white shadow-md"
                                                : "text-gray-600 hover:bg-[#439454] hover:text-white hover:shadow-md"
                                        }`}
                                        onClick={() => {
                                            if (item.submenu) {
                                                toggleSubmenu(index);
                                            }
                                        }}
                                    >
                                        <Link
                                            href={item.href}
                                            className="flex items-center flex-1 space-x-3"
                                        >
                                            {item.icon}
                                            <span className="font-medium">
                                                {item.name}
                                            </span>
                                        </Link>
                                        {item.submenu && (
                                            <svg
                                                className={`w-4 h-4 transition-transform duration-200 ${
                                                    activeSubmenu === index
                                                        ? "transform rotate-180"
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
                                    {item.submenu &&
                                        activeSubmenu === index && (
                                            <div className="mt-2 ml-4 space-y-1">
                                                {item.submenu.map(
                                                    (subItem, subIndex) => (
                                                        <Link
                                                            key={subIndex}
                                                            href={subItem.href}
                                                            className="block px-4 py-2 text-sm text-gray-600 hover:bg-[#439454] hover:text-white rounded-lg transition-all duration-200"
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

                    {/* Profile Section - Fixed */}
                    <div className="p-4 bg-white border-t border-gray-200">
                        <div className="flex items-center space-x-3">
                            <div className="flex items-center justify-center w-10 h-10 rounded-full bg-gradient-to-br from-purple-400 to-purple-600">
                                <span className="text-sm font-bold text-white">
                                    GD
                                </span>
                            </div>
                            <div>
                                <p className="text-sm font-medium text-gray-800">
                                    GusDek
                                </p>
                                <p className="text-xs text-gray-500">Jabatan</p>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Main Content */}
                <div className="flex-1 ml-64 overflow-auto">{children}</div>
            </div>
        </>
    );
};

export default DashboardLayout;
