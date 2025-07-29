import DashboardLayout from "../Layouts/DashboardLayout";

const Settings = () => {
    return (
        <DashboardLayout title="Pengaturan">
            <div className="p-6">
                <div className="p-6 bg-white rounded-lg shadow-sm">
                    <h1 className="mb-4 text-2xl font-bold text-gray-800">
                        Pengaturan Sistem
                    </h1>
                    <p className="text-gray-600">
                        Halaman untuk mengatur konfigurasi sistem akan segera
                        tersedia.
                    </p>
                </div>
            </div>
        </DashboardLayout>
    );
};

export default Settings;
