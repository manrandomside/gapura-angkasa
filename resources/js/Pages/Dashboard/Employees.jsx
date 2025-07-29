import DashboardLayout from "../Layouts/DashboardLayout";

const Employees = () => {
    return (
        <DashboardLayout title="Data Karyawan">
            <div className="p-6">
                <div className="p-6 bg-white rounded-lg shadow-sm">
                    <h1 className="mb-4 text-2xl font-bold text-gray-800">
                        Data Karyawan
                    </h1>
                    <p className="text-gray-600">
                        Halaman untuk mengelola data karyawan akan segera
                        tersedia.
                    </p>
                </div>
            </div>
        </DashboardLayout>
    );
};

export default Employees;
