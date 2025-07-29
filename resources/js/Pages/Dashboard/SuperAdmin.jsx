import DashboardLayout from "../../Layouts/DashboardLayout";

const SuperAdmin = ({
    total_employees,
    active_employees,
    active_percentage,
    organization_units,
    organizations,
    current_role,
    user_profile,
}) => {
    const quickActions = [
        {
            title: "Tambah Karyawan",
            description: "Daftarkan karyawan baru",
            icon: (
                <svg
                    className="w-6 h-6"
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
            color: "bg-blue-500 hover:bg-blue-600",
            href: "/data-karyawan/create",
        },
        {
            title: "Generate Laporan",
            description: "Buat laporan bulanan",
            icon: (
                <svg
                    className="w-6 h-6"
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
            color: "bg-pink-500 hover:bg-pink-600",
            href: "/laporan/generate",
        },
        {
            title: "System Settings",
            description: "Kelola pengaturan sistem",
            icon: (
                <svg
                    className="w-6 h-6"
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
            color: "bg-cyan-500 hover:bg-cyan-600",
            href: "/pengaturan",
        },
    ];

    return (
        <DashboardLayout title="Dashboard SDM">
            <div className="p-6">
                {/* Header */}
                <div className="p-6 mb-6 bg-white rounded-lg shadow-sm">
                    <div className="flex items-center justify-between">
                        <div>
                            <h1 className="text-2xl font-bold text-gray-800">
                                Dashboard SDM
                            </h1>
                            <p className="mt-1 text-gray-600">
                                Selamat datang di sistem manajemen SDM GAPURA
                                ANGKASA
                            </p>
                        </div>
                        <div className="flex items-center space-x-4">
                            <div className="text-right">
                                <p className="text-sm text-gray-500">
                                    Simulasi Role:
                                </p>
                                <p className="text-sm font-medium text-gray-800">
                                    {user_profile?.email || "admin@gapura.com"}
                                </p>
                            </div>
                            <div className="bg-[#439454] text-white px-4 py-2 rounded-lg text-sm font-medium">
                                {current_role || "Super Admin"}
                            </div>
                        </div>
                    </div>
                </div>

                {/* Stats Cards */}
                <div className="grid grid-cols-1 gap-6 mb-6 md:grid-cols-2 lg:grid-cols-4">
                    {/* Total Karyawan */}
                    <div className="p-6 bg-white rounded-lg shadow-sm">
                        <div className="flex items-center">
                            <div className="flex items-center justify-center w-12 h-12 bg-blue-100 rounded-lg">
                                <svg
                                    className="w-6 h-6 text-blue-600"
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
                            <div className="flex-1 ml-4">
                                <p className="text-2xl font-bold text-gray-800">
                                    {total_employees || 0}
                                </p>
                                <p className="text-gray-600">Total Karyawan</p>
                                <p className="mt-1 text-sm text-green-600">
                                    +5 bulan ini
                                </p>
                            </div>
                        </div>
                    </div>

                    {/* Karyawan Aktif */}
                    <div className="p-6 bg-white rounded-lg shadow-sm">
                        <div className="flex items-center">
                            <div className="flex items-center justify-center w-12 h-12 bg-green-100 rounded-lg">
                                <svg
                                    className="w-6 h-6 text-green-600"
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
                            <div className="flex-1 ml-4">
                                <p className="text-2xl font-bold text-gray-800">
                                    {active_employees || 0}
                                </p>
                                <p className="text-gray-600">Karyawan Aktif</p>
                                <p className="mt-1 text-sm text-green-600">
                                    {active_percentage || 0}% dari total
                                </p>
                            </div>
                        </div>
                    </div>

                    {/* Unit Organisasi */}
                    <div className="p-6 bg-white rounded-lg shadow-sm">
                        <div className="flex items-center">
                            <div className="flex items-center justify-center w-12 h-12 bg-purple-100 rounded-lg">
                                <svg
                                    className="w-6 h-6 text-purple-600"
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
                            <div className="flex-1 ml-4">
                                <p className="text-2xl font-bold text-gray-800">
                                    {organization_units || 0}
                                </p>
                                <p className="text-gray-600">Unit Organisasi</p>
                                <p className="mt-1 text-sm text-green-600">
                                    Tersebar di bandara
                                </p>
                            </div>
                        </div>
                    </div>

                    {/* Data Access */}
                    <div className="p-6 bg-white rounded-lg shadow-sm">
                        <div className="flex items-center">
                            <div className="flex items-center justify-center w-12 h-12 rounded-lg bg-cyan-100">
                                <svg
                                    className="w-6 h-6 text-cyan-600"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth={2}
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                                    />
                                </svg>
                            </div>
                            <div className="flex-1 ml-4">
                                <p className="text-2xl font-bold text-gray-800">
                                    100%
                                </p>
                                <p className="text-gray-600">Data Access</p>
                                <p className="mt-1 text-sm text-green-600">
                                    Full system access
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Info Access Banner */}
                <div className="p-4 mb-6 border border-yellow-200 rounded-lg bg-yellow-50">
                    <div className="flex items-center">
                        <svg
                            className="w-5 h-5 mr-3 text-yellow-600"
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
                        <p className="font-medium text-yellow-800">
                            Informasi Akses: Anda memiliki akses penuh ke semua
                            fitur sistem sebagai Super Admin.
                        </p>
                    </div>
                </div>

                {/* Quick Actions */}
                <div className="p-6 bg-white rounded-lg shadow-sm">
                    <h2 className="mb-4 text-lg font-semibold text-gray-800">
                        Quick Actions
                    </h2>
                    <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
                        {quickActions.map((action, index) => (
                            <div
                                key={index}
                                className={`${action.color} text-white rounded-lg p-6 cursor-pointer transition-all duration-200 transform hover:scale-105 hover:shadow-lg`}
                            >
                                <div className="flex items-center mb-3">
                                    {action.icon}
                                    <h3 className="ml-3 text-lg font-semibold">
                                        {action.title}
                                    </h3>
                                </div>
                                <p className="text-sm opacity-90">
                                    {action.description}
                                </p>
                            </div>
                        ))}
                    </div>
                </div>
            </div>
        </DashboardLayout>
    );
};

export default SuperAdmin;
