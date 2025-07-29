import DashboardLayout from "../Layouts/DashboardLayout";

const Reports = () => {
    return (
        <DashboardLayout title="Laporan">
            <div className="p-6">
                <div className="p-6 bg-white rounded-lg shadow-sm">
                    <h1 className="mb-4 text-2xl font-bold text-gray-800">
                        Laporan
                    </h1>
                    <p className="text-gray-600">
                        Halaman untuk generate dan melihat laporan akan segera
                        tersedia.
                    </p>
                </div>
            </div>
        </DashboardLayout>
    );
};

export default Reports;
