import DashboardLayout from "@/Layouts/DashboardLayout";

export default function SuperAdminDashboard({
    total_employees = 202,
    active_employees = 198,
    active_percentage = 98,
    organization_units = 8,
    current_role = "Super Admin",
    user_profile = { name: "GusDek", email: "admin@gapura.com" },
}) {
    const quickActions = [
        {
            title: "Tambah Karyawan",
            description: "Daftarkan karyawan baru",
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
            gradient: "from-blue-500 to-blue-600",
            href: "/data-karyawan/create",
        },
        {
            title: "Generate Laporan",
            description: "Buat laporan bulanan",
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
            href: "/laporan/generate",
        },
        {
            title: "System Settings",
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
            <div className="min-h-screen bg-gradient-to-br from-gray-50 via-white to-gray-50">
                {/* Header Section */}
                <div className="relative overflow-hidden bg-white shadow-sm">
                    <div className="absolute inset-0 bg-gradient-to-r from-[#439454]/5 to-transparent"></div>
                    <div className="relative px-8 py-10">
                        <div className="max-w-4xl">
                            <h1 className="mb-3 text-4xl font-bold text-gray-900">
                                Dashboard SDM
                            </h1>
                            <p className="mb-6 text-lg text-gray-600">
                                Selamat datang di sistem manajemen SDM GAPURA
                                ANGKASA
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
                                    Informasi Akses: Anda memiliki akses penuh
                                    ke semua fitur sistem sebagai Super Admin
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div className="px-8 pb-8 -mt-4">
                    {/* Stats Cards */}
                    <div className="relative z-10 grid grid-cols-1 gap-8 mb-12 md:grid-cols-2 lg:grid-cols-4">
                        {/* Total Karyawan */}
                        <div className="relative overflow-hidden transition-all duration-500 transform bg-white border border-gray-100 shadow-xl stats-card group rounded-3xl hover:shadow-2xl hover:-translate-y-3">
                            <div className="absolute inset-0 transition-opacity duration-500 opacity-0 bg-gradient-to-br from-blue-500/5 to-blue-600/10 group-hover:opacity-100"></div>
                            <div className="relative p-8">
                                <div className="flex items-center justify-between mb-8">
                                    <div className="flex items-center justify-center transition-all duration-500 shadow-2xl w-18 h-18 bg-gradient-to-br from-blue-500 to-blue-600 rounded-3xl group-hover:scale-110 group-hover:rotate-3">
                                        <svg
                                            className="text-white w-9 h-9"
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
                                    </div>
                                </div>
                                <div>
                                    <p className="mb-3 text-4xl font-bold text-gray-900 transition-colors duration-500 group-hover:text-blue-600">
                                        {total_employees}
                                    </p>
                                    <p className="mb-2 text-lg font-semibold text-gray-600">
                                        Total Karyawan
                                    </p>
                                    <p className="inline-block px-3 py-1 text-sm font-medium text-blue-600 rounded-full bg-blue-50">
                                        +5 bulan ini
                                    </p>
                                </div>
                            </div>
                        </div>

                        {/* Karyawan Aktif */}
                        <div className="relative overflow-hidden transition-all duration-500 transform bg-white border border-gray-100 shadow-xl stats-card group rounded-3xl hover:shadow-2xl hover:-translate-y-3">
                            <div className="absolute inset-0 transition-opacity duration-500 opacity-0 bg-gradient-to-br from-green-500/5 to-green-600/10 group-hover:opacity-100"></div>
                            <div className="relative p-8">
                                <div className="flex items-center justify-between mb-8">
                                    <div className="flex items-center justify-center transition-all duration-500 shadow-2xl w-18 h-18 bg-gradient-to-br from-green-500 to-green-600 rounded-3xl group-hover:scale-110 group-hover:rotate-3">
                                        <svg
                                            className="text-white w-9 h-9"
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
                                <div>
                                    <p className="mb-3 text-4xl font-bold text-gray-900 transition-colors duration-500 group-hover:text-green-600">
                                        {active_employees}
                                    </p>
                                    <p className="mb-2 text-lg font-semibold text-gray-600">
                                        Karyawan Aktif
                                    </p>
                                    <p className="inline-block px-3 py-1 text-sm font-medium text-green-600 rounded-full bg-green-50">
                                        {active_percentage}% dari total
                                    </p>
                                </div>
                            </div>
                        </div>

                        {/* Unit Organisasi */}
                        <div className="relative overflow-hidden transition-all duration-500 transform bg-white border border-gray-100 shadow-xl stats-card group rounded-3xl hover:shadow-2xl hover:-translate-y-3">
                            <div className="absolute inset-0 transition-opacity duration-500 opacity-0 bg-gradient-to-br from-purple-500/5 to-purple-600/10 group-hover:opacity-100"></div>
                            <div className="relative p-8">
                                <div className="flex items-center justify-between mb-8">
                                    <div className="flex items-center justify-center transition-all duration-500 shadow-2xl w-18 h-18 bg-gradient-to-br from-purple-500 to-purple-600 rounded-3xl group-hover:scale-110 group-hover:rotate-3">
                                        <svg
                                            className="text-white w-9 h-9"
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
                                <div>
                                    <p className="mb-3 text-4xl font-bold text-gray-900 transition-colors duration-500 group-hover:text-purple-600">
                                        {organization_units}
                                    </p>
                                    <p className="mb-2 text-lg font-semibold text-gray-600">
                                        Unit Organisasi
                                    </p>
                                    <p className="inline-block px-3 py-1 text-sm font-medium text-purple-600 rounded-full bg-purple-50">
                                        Tersebar di bandara
                                    </p>
                                </div>
                            </div>
                        </div>

                        {/* Data Access */}
                        <div className="relative overflow-hidden transition-all duration-500 transform bg-white border border-gray-100 shadow-xl stats-card group rounded-3xl hover:shadow-2xl hover:-translate-y-3">
                            <div className="absolute inset-0 transition-opacity duration-500 opacity-0 bg-gradient-to-br from-cyan-500/5 to-cyan-600/10 group-hover:opacity-100"></div>
                            <div className="relative p-8">
                                <div className="flex items-center justify-between mb-8">
                                    <div className="flex items-center justify-center transition-all duration-500 shadow-2xl w-18 h-18 bg-gradient-to-br from-cyan-500 to-cyan-600 rounded-3xl group-hover:scale-110 group-hover:rotate-3">
                                        <svg
                                            className="text-white w-9 h-9"
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
                                    </div>
                                </div>
                                <div>
                                    <p className="mb-3 text-4xl font-bold text-gray-900 transition-colors duration-500 group-hover:text-cyan-600">
                                        100%
                                    </p>
                                    <p className="mb-2 text-lg font-semibold text-gray-600">
                                        Data Access
                                    </p>
                                    <p className="inline-block px-3 py-1 text-sm font-medium rounded-full text-cyan-600 bg-cyan-50">
                                        Full system access
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Quick Actions */}
                    <div className="mb-10">
                        <div className="mb-8 text-center">
                            <h2 className="mb-3 text-3xl font-bold text-gray-900">
                                Quick Actions
                            </h2>
                            <p className="text-lg text-gray-600">
                                Akses cepat ke fitur utama sistem
                            </p>
                        </div>
                        <div className="grid grid-cols-1 gap-8 mx-auto md:grid-cols-3 max-w-7xl">
                            {quickActions.map((action, index) => (
                                <a
                                    key={index}
                                    href={action.href}
                                    className="relative overflow-hidden transition-all duration-700 transform bg-white border border-gray-100 shadow-xl quick-action-card group rounded-3xl hover:shadow-2xl hover:-translate-y-4"
                                >
                                    <div
                                        className={`absolute inset-0 bg-gradient-to-br ${action.gradient} opacity-0 group-hover:opacity-100 transition-opacity duration-700`}
                                    ></div>
                                    <div className="relative p-10">
                                        <div className="flex flex-col items-center text-center">
                                            <div
                                                className={`flex items-center justify-center w-24 h-24 bg-gradient-to-br ${action.gradient} rounded-3xl shadow-2xl mb-8 group-hover:scale-110 group-hover:rotate-3 transition-all duration-700`}
                                            >
                                                <div className="text-white">
                                                    {action.icon}
                                                </div>
                                            </div>
                                            <h3 className="mb-3 text-2xl font-bold text-gray-900 transition-colors duration-700 group-hover:text-white">
                                                {action.title}
                                            </h3>
                                            <p className="text-lg leading-relaxed text-gray-600 transition-colors duration-700 group-hover:text-white/90">
                                                {action.description}
                                            </p>
                                            <div className="mt-6 transition-all duration-700 transform translate-y-4 opacity-0 group-hover:opacity-100 group-hover:translate-y-0">
                                                <div className="flex items-center font-medium text-white">
                                                    <span className="mr-2">
                                                        Akses Sekarang
                                                    </span>
                                                    <svg
                                                        className="w-5 h-5 transition-transform duration-300 transform group-hover:translate-x-1"
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
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    {/* Decorative Elements */}
                                    <div className="absolute w-20 h-20 transition-opacity duration-700 rounded-full opacity-0 top-4 right-4 bg-white/5 group-hover:opacity-100"></div>
                                    <div className="absolute w-16 h-16 transition-opacity duration-700 delay-100 rounded-full opacity-0 bottom-4 left-4 bg-white/5 group-hover:opacity-100"></div>
                                </a>
                            ))}
                        </div>
                    </div>

                    {/* Welcome Message */}
                    <div className="bg-gradient-to-r from-[#439454] to-[#358945] rounded-3xl shadow-2xl p-10 text-center text-white">
                        <div className="max-w-3xl mx-auto">
                            <h3 className="mb-4 text-3xl font-bold">
                                Selamat Bekerja, {user_profile.name}!
                            </h3>
                            <p className="text-lg leading-relaxed text-white/90">
                                Sistem manajemen SDM GAPURA ANGKASA siap
                                membantu Anda mengelola sumber daya manusia
                                dengan efisien. Gunakan menu navigasi di sebelah
                                kiri untuk mengakses berbagai fitur yang
                                tersedia.
                            </p>
                            <div className="flex items-center justify-center mt-8 space-x-6">
                                <div className="flex items-center">
                                    <div className="w-3 h-3 mr-2 bg-white rounded-full animate-pulse"></div>
                                    <span className="text-white/90">
                                        Sistem Online
                                    </span>
                                </div>
                                <div className="flex items-center">
                                    <div className="w-3 h-3 mr-2 bg-white rounded-full"></div>
                                    <span className="text-white/90">
                                        Data Tersinkronisasi
                                    </span>
                                </div>
                                <div className="flex items-center">
                                    <div className="w-3 h-3 mr-2 bg-white rounded-full"></div>
                                    <span className="text-white/90">
                                        Keamanan Aktif
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </DashboardLayout>
    );
}
