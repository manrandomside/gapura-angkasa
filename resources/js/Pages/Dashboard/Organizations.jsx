import DashboardLayout from "../Layouts/DashboardLayout";

const Organizations = () => {
    return (
        <DashboardLayout title="Organisasi">
            <div className="p-6">
                <div className="p-6 bg-white rounded-lg shadow-sm">
                    <h1 className="mb-4 text-2xl font-bold text-gray-800">
                        Unit Organisasi
                    </h1>
                    <p className="text-gray-600">
                        Halaman untuk mengelola unit organisasi akan segera
                        tersedia.
                    </p>
                </div>
            </div>
        </DashboardLayout>
    );
};

export default Organizations;
