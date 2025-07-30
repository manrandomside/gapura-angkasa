import React, { useState, useEffect } from "react";
import { Head, Link, router } from "@inertiajs/react";
import DashboardLayout from "../../Layouts/DashboardLayout";
import {
    Search,
    Filter,
    Plus,
    Download,
    Upload,
    Eye,
    Edit2,
    Trash2,
    User,
    Badge,
    Calendar,
    Phone,
    Mail,
    MapPin,
    X,
    Building,
    Users,
} from "lucide-react";

export default function Index({
    employees = [],
    organizations = [],
    filters = {},
    success,
    error,
    info,
}) {
    const [searchTerm, setSearchTerm] = useState(filters.search || "");
    const [statusFilter, setStatusFilter] = useState(
        filters.status_pegawai || "all"
    );
    const [unitFilter, setUnitFilter] = useState(
        filters.unit_organisasi || "all"
    );
    const [genderFilter, setGenderFilter] = useState(
        filters.jenis_kelamin || "all"
    );
    const [showEmployeeModal, setShowEmployeeModal] = useState(false);
    const [selectedEmployee, setSelectedEmployee] = useState(null);
    const [isLoading, setIsLoading] = useState(false);

    // Handle search submission
    const handleSearch = (e) => {
        e.preventDefault();
        applyFilters();
    };

    // Handle filter change
    const handleFilterChange = (filterType, value) => {
        switch (filterType) {
            case "status":
                setStatusFilter(value);
                break;
            case "unit":
                setUnitFilter(value);
                break;
            case "gender":
                setGenderFilter(value);
                break;
        }

        // Auto-apply filters after a short delay
        setTimeout(() => {
            applyFilters();
        }, 100);
    };

    // Apply all filters
    const applyFilters = () => {
        setIsLoading(true);

        const params = {};
        if (searchTerm.trim()) params.search = searchTerm.trim();
        if (statusFilter !== "all") params.status_pegawai = statusFilter;
        if (unitFilter !== "all") params.unit_organisasi = unitFilter;
        if (genderFilter !== "all") params.jenis_kelamin = genderFilter;

        router.get(route("employees.index"), params, {
            preserveState: true,
            preserveScroll: true,
            onFinish: () => setIsLoading(false),
        });
    };

    // Clear all filters
    const clearFilters = () => {
        setSearchTerm("");
        setStatusFilter("all");
        setUnitFilter("all");
        setGenderFilter("all");
        setIsLoading(true);

        router.get(
            route("employees.index"),
            {},
            {
                preserveState: true,
                preserveScroll: true,
                onFinish: () => setIsLoading(false),
            }
        );
    };

    // Handle individual filter removal
    const removeFilter = (filterType) => {
        switch (filterType) {
            case "search":
                setSearchTerm("");
                break;
            case "status":
                setStatusFilter("all");
                break;
            case "unit":
                setUnitFilter("all");
                break;
            case "gender":
                setGenderFilter("all");
                break;
        }

        setTimeout(() => {
            applyFilters();
        }, 100);
    };

    // Get initials for avatar
    const getInitials = (name) => {
        if (!name) return "??";
        return name
            .split(" ")
            .map((n) => n[0])
            .join("")
            .toUpperCase()
            .slice(0, 2);
    };

    // Format date
    const formatDate = (dateString) => {
        if (!dateString) return "-";
        try {
            return new Date(dateString).toLocaleDateString("id-ID", {
                day: "2-digit",
                month: "2-digit",
                year: "numeric",
            });
        } catch {
            return "-";
        }
    };

    // Show employee details modal
    const showEmployeeDetails = (employee) => {
        setSelectedEmployee(employee);
        setShowEmployeeModal(true);
    };

    // Close modal
    const closeModal = () => {
        setShowEmployeeModal(false);
        setSelectedEmployee(null);
    };

    // Get unique units from employees for filter
    const getUniqueUnits = () => {
        const units = [
            ...new Set(
                employees.map((emp) => emp.unit_organisasi).filter(Boolean)
            ),
        ];
        return units.sort();
    };

    return (
        <DashboardLayout title="Management Karyawan">
            <Head title="Management Karyawan - GAPURA ANGKASA SDM" />

            <div className="min-h-screen bg-gray-50">
                {/* Header Section - Fixed/Sticky */}
                <div className="sticky top-0 z-40 bg-white border-b border-gray-200 shadow-sm">
                    <div className="px-6 py-6">
                        <div className="flex flex-col md:flex-row md:items-center md:justify-between">
                            <div>
                                <h1 className="text-2xl font-bold text-gray-900">
                                    Management Karyawan
                                </h1>
                                <p className="mt-1 text-sm text-gray-600">
                                    Kelola data karyawan PT Gapura Angkasa -
                                    Bandar Udara Ngurah Rai
                                </p>
                            </div>
                            <div className="flex gap-3 mt-4 md:mt-0">
                                <Link
                                    href={route("employees.import")}
                                    className="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 transition-colors duration-200 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
                                >
                                    <Upload className="w-4 h-4" />
                                    Import Data
                                </Link>
                                <Link
                                    href={route("employees.export")}
                                    className="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 transition-colors duration-200 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
                                >
                                    <Download className="w-4 h-4" />
                                    Export Data
                                </Link>
                                <Link
                                    href={route("employees.create")}
                                    className="inline-flex items-center gap-2 px-4 py-2 bg-[#439454] text-white rounded-lg text-sm font-medium hover:bg-[#3a7d46] transition-colors duration-200"
                                >
                                    <Plus className="w-4 h-4" />
                                    Tambah Karyawan
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <div className="p-6">
                    {/* Statistics Cards */}
                    <div className="grid grid-cols-1 gap-6 mb-6 md:grid-cols-4">
                        <div className="p-6 bg-white border border-gray-200 rounded-lg shadow-sm">
                            <div className="flex items-center justify-between">
                                <div>
                                    <p className="text-sm font-medium text-gray-600">
                                        Total Karyawan
                                    </p>
                                    <p className="text-2xl font-bold text-gray-900">
                                        {employees.length}
                                    </p>
                                </div>
                                <div className="p-3 bg-blue-100 rounded-lg">
                                    <User className="w-6 h-6 text-blue-600" />
                                </div>
                            </div>
                        </div>

                        <div className="p-6 bg-white border border-gray-200 rounded-lg shadow-sm">
                            <div className="flex items-center justify-between">
                                <div>
                                    <p className="text-sm font-medium text-gray-600">
                                        Pegawai Tetap
                                    </p>
                                    <p className="text-2xl font-bold text-gray-900">
                                        {
                                            employees.filter(
                                                (emp) =>
                                                    emp.status_pegawai ===
                                                    "PEGAWAI TETAP"
                                            ).length
                                        }
                                    </p>
                                </div>
                                <div className="p-3 bg-green-100 rounded-lg">
                                    <Badge className="w-6 h-6 text-green-600" />
                                </div>
                            </div>
                        </div>

                        <div className="p-6 bg-white border border-gray-200 rounded-lg shadow-sm">
                            <div className="flex items-center justify-between">
                                <div>
                                    <p className="text-sm font-medium text-gray-600">
                                        TAD
                                    </p>
                                    <p className="text-2xl font-bold text-gray-900">
                                        {
                                            employees.filter(
                                                (emp) =>
                                                    emp.status_pegawai === "TAD"
                                            ).length
                                        }
                                    </p>
                                </div>
                                <div className="p-3 bg-yellow-100 rounded-lg">
                                    <Calendar className="w-6 h-6 text-yellow-600" />
                                </div>
                            </div>
                        </div>

                        <div className="p-6 bg-white border border-gray-200 rounded-lg shadow-sm">
                            <div className="flex items-center justify-between">
                                <div>
                                    <p className="text-sm font-medium text-gray-600">
                                        Unit Organisasi
                                    </p>
                                    <p className="text-2xl font-bold text-gray-900">
                                        {getUniqueUnits().length}
                                    </p>
                                </div>
                                <div className="p-3 bg-purple-100 rounded-lg">
                                    <Building className="w-6 h-6 text-purple-600" />
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Success/Error Messages */}
                    {success && (
                        <div className="p-4 mb-6 text-green-800 bg-green-100 border border-green-200 rounded-lg">
                            <div className="flex items-center">
                                <div className="flex-shrink-0">
                                    <svg
                                        className="w-5 h-5 text-green-400"
                                        fill="currentColor"
                                        viewBox="0 0 20 20"
                                    >
                                        <path
                                            fillRule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                            clipRule="evenodd"
                                        />
                                    </svg>
                                </div>
                                <div className="ml-3">
                                    <p className="text-sm font-medium">
                                        {success}
                                    </p>
                                </div>
                            </div>
                        </div>
                    )}

                    {error && (
                        <div className="p-4 mb-6 text-red-800 bg-red-100 border border-red-200 rounded-lg">
                            <div className="flex items-center">
                                <div className="flex-shrink-0">
                                    <svg
                                        className="w-5 h-5 text-red-400"
                                        fill="currentColor"
                                        viewBox="0 0 20 20"
                                    >
                                        <path
                                            fillRule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                            clipRule="evenodd"
                                        />
                                    </svg>
                                </div>
                                <div className="ml-3">
                                    <p className="text-sm font-medium">
                                        {error}
                                    </p>
                                </div>
                            </div>
                        </div>
                    )}

                    {/* Search and Filters - Normal (not sticky) */}
                    <div className="p-6 mb-6 bg-white border border-gray-200 rounded-lg shadow-sm">
                        <form onSubmit={handleSearch} className="space-y-4">
                            {/* Search Input dengan improved functionality */}
                            <div className="relative">
                                <div className="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <Search className="w-5 h-5 text-gray-400" />
                                </div>
                                <input
                                    type="text"
                                    placeholder="Cari berdasarkan NIP, nama, jabatan, atau unit organisasi..."
                                    value={searchTerm}
                                    onChange={(e) =>
                                        setSearchTerm(e.target.value)
                                    }
                                    onKeyDown={(e) => {
                                        if (e.key === "Enter") {
                                            e.preventDefault();
                                            handleSearch(e);
                                        }
                                    }}
                                    className="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#439454] focus:border-transparent transition-all duration-200"
                                />
                                {searchTerm && (
                                    <div className="absolute inset-y-0 right-0 flex items-center pr-3">
                                        <button
                                            onClick={() =>
                                                removeFilter("search")
                                            }
                                            className="p-1 text-gray-400 rounded-full hover:text-gray-600 hover:bg-gray-100"
                                            title="Hapus pencarian"
                                        >
                                            <X className="w-4 h-4" />
                                        </button>
                                    </div>
                                )}
                            </div>

                            {/* Filter Row */}
                            <div className="grid grid-cols-1 gap-4 md:grid-cols-4">
                                <div>
                                    <label className="block mb-2 text-sm font-medium text-gray-700">
                                        Status Pegawai
                                    </label>
                                    <select
                                        value={statusFilter}
                                        onChange={(e) =>
                                            handleFilterChange(
                                                "status",
                                                e.target.value
                                            )
                                        }
                                        className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#439454] focus:border-transparent"
                                    >
                                        <option value="all">
                                            Semua Status
                                        </option>
                                        <option value="PEGAWAI TETAP">
                                            Pegawai Tetap
                                        </option>
                                        <option value="TAD">TAD</option>
                                    </select>
                                </div>

                                <div>
                                    <label className="block mb-2 text-sm font-medium text-gray-700">
                                        Unit Organisasi
                                    </label>
                                    <select
                                        value={unitFilter}
                                        onChange={(e) =>
                                            handleFilterChange(
                                                "unit",
                                                e.target.value
                                            )
                                        }
                                        className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#439454] focus:border-transparent"
                                    >
                                        <option value="all">Semua Unit</option>
                                        {getUniqueUnits().map((unit) => (
                                            <option key={unit} value={unit}>
                                                {unit}
                                            </option>
                                        ))}
                                    </select>
                                </div>

                                <div>
                                    <label className="block mb-2 text-sm font-medium text-gray-700">
                                        Jenis Kelamin
                                    </label>
                                    <select
                                        value={genderFilter}
                                        onChange={(e) =>
                                            handleFilterChange(
                                                "gender",
                                                e.target.value
                                            )
                                        }
                                        className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#439454] focus:border-transparent"
                                    >
                                        <option value="all">Semua</option>
                                        <option value="Laki-laki">
                                            Laki-laki
                                        </option>
                                        <option value="Perempuan">
                                            Perempuan
                                        </option>
                                    </select>
                                </div>

                                <div className="flex items-end gap-2">
                                    <button
                                        type="submit"
                                        disabled={isLoading}
                                        className="flex-1 px-4 py-2 bg-[#439454] text-white rounded-lg hover:bg-[#3a7d46] transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                                    >
                                        {isLoading ? (
                                            <>
                                                <div className="w-4 h-4 border-2 border-white rounded-full border-t-transparent animate-spin"></div>
                                                Mencari...
                                            </>
                                        ) : (
                                            <>
                                                <Search className="w-4 h-4" />
                                                Cari
                                            </>
                                        )}
                                    </button>
                                    <button
                                        type="button"
                                        onClick={clearFilters}
                                        className="px-4 py-2 text-gray-600 transition-colors duration-200 bg-gray-100 rounded-lg hover:bg-gray-200"
                                    >
                                        Reset
                                    </button>
                                </div>
                            </div>
                        </form>

                        <div className="flex items-center justify-between mt-4">
                            {/* Active Filters */}
                            <div className="flex flex-wrap gap-2">
                                {searchTerm && (
                                    <span className="inline-flex items-center gap-1 px-3 py-1 text-xs font-medium text-blue-800 bg-blue-100 rounded-full">
                                        Pencarian: "{searchTerm}"
                                        <button
                                            onClick={() =>
                                                removeFilter("search")
                                            }
                                            className="ml-1 text-blue-600 hover:text-blue-800"
                                        >
                                            <X className="w-3 h-3" />
                                        </button>
                                    </span>
                                )}
                                {statusFilter !== "all" && (
                                    <span className="inline-flex items-center gap-1 px-3 py-1 text-xs font-medium text-green-800 bg-green-100 rounded-full">
                                        Status: {statusFilter}
                                        <button
                                            onClick={() =>
                                                removeFilter("status")
                                            }
                                            className="ml-1 text-green-600 hover:text-green-800"
                                        >
                                            <X className="w-3 h-3" />
                                        </button>
                                    </span>
                                )}
                                {unitFilter !== "all" && (
                                    <span className="inline-flex items-center gap-1 px-3 py-1 text-xs font-medium text-purple-800 bg-purple-100 rounded-full">
                                        Unit: {unitFilter}
                                        <button
                                            onClick={() => removeFilter("unit")}
                                            className="ml-1 text-purple-600 hover:text-purple-800"
                                        >
                                            <X className="w-3 h-3" />
                                        </button>
                                    </span>
                                )}
                                {genderFilter !== "all" && (
                                    <span className="inline-flex items-center gap-1 px-3 py-1 text-xs font-medium text-orange-800 bg-orange-100 rounded-full">
                                        Gender: {genderFilter}
                                        <button
                                            onClick={() =>
                                                removeFilter("gender")
                                            }
                                            className="ml-1 text-orange-600 hover:text-orange-800"
                                        >
                                            <X className="w-3 h-3" />
                                        </button>
                                    </span>
                                )}
                            </div>

                            {/* Results Count */}
                            <div className="flex items-center text-sm text-gray-600">
                                Menampilkan {employees.length} karyawan
                            </div>
                        </div>
                    </div>

                    {/* Employee Table */}
                    <div className="overflow-hidden bg-white border border-gray-200 rounded-lg shadow-sm">
                        <div className="overflow-x-auto">
                            <table className="w-full">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                            NIP
                                        </th>
                                        <th className="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                            Nama Lengkap
                                        </th>
                                        <th className="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                            Status Pegawai
                                        </th>
                                        <th className="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                            TMT Mulai Jabatan
                                        </th>
                                        <th className="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                            Aksi
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="bg-white divide-y divide-gray-200">
                                    {employees.length > 0 ? (
                                        employees.map((employee) => (
                                            <tr
                                                key={employee.id}
                                                className="transition-colors duration-200 hover:bg-gray-50"
                                            >
                                                <td className="px-6 py-4 text-sm font-medium text-gray-900 whitespace-nowrap">
                                                    {employee.nip}
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap">
                                                    <div className="flex items-center">
                                                        <div className="flex-shrink-0 w-10 h-10">
                                                            <div className="h-10 w-10 rounded-full bg-[#439454] flex items-center justify-center">
                                                                <span className="text-sm font-medium text-white">
                                                                    {getInitials(
                                                                        employee.nama_lengkap
                                                                    )}
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div className="ml-4">
                                                            <div className="text-sm font-medium text-gray-900">
                                                                {
                                                                    employee.nama_lengkap
                                                                }
                                                            </div>
                                                            <div className="text-sm text-gray-500">
                                                                {
                                                                    employee.unit_organisasi
                                                                }{" "}
                                                                -{" "}
                                                                {employee.jabatan ||
                                                                    employee.nama_jabatan}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap">
                                                    <span
                                                        className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${
                                                            employee.status_pegawai ===
                                                            "PEGAWAI TETAP"
                                                                ? "bg-green-100 text-green-800"
                                                                : "bg-yellow-100 text-yellow-800"
                                                        }`}
                                                    >
                                                        {
                                                            employee.status_pegawai
                                                        }
                                                    </span>
                                                </td>
                                                <td className="px-6 py-4 text-sm text-gray-900 whitespace-nowrap">
                                                    {formatDate(
                                                        employee.tmt_mulai_jabatan
                                                    )}
                                                </td>
                                                <td className="px-6 py-4 text-sm font-medium whitespace-nowrap">
                                                    <div className="flex items-center gap-2">
                                                        <button
                                                            onClick={() =>
                                                                showEmployeeDetails(
                                                                    employee
                                                                )
                                                            }
                                                            className="p-1 text-blue-600 transition-colors duration-200 rounded hover:text-blue-900"
                                                            title="Lihat Detail"
                                                        >
                                                            <Eye className="w-4 h-4" />
                                                        </button>
                                                        <Link
                                                            href={route(
                                                                "employees.edit",
                                                                employee.id
                                                            )}
                                                            className="p-1 text-indigo-600 transition-colors duration-200 rounded hover:text-indigo-900"
                                                            title="Edit"
                                                        >
                                                            <Edit2 className="w-4 h-4" />
                                                        </Link>
                                                        <button
                                                            onClick={() => {
                                                                if (
                                                                    confirm(
                                                                        `Apakah Anda yakin ingin menghapus data karyawan ${employee.nama_lengkap}?`
                                                                    )
                                                                ) {
                                                                    router.delete(
                                                                        route(
                                                                            "employees.destroy",
                                                                            employee.id
                                                                        )
                                                                    );
                                                                }
                                                            }}
                                                            className="p-1 text-red-600 transition-colors duration-200 rounded hover:text-red-900"
                                                            title="Hapus"
                                                        >
                                                            <Trash2 className="w-4 h-4" />
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        ))
                                    ) : (
                                        <tr>
                                            <td
                                                colSpan="5"
                                                className="px-6 py-12 text-center text-gray-500"
                                            >
                                                <div className="flex flex-col items-center">
                                                    <User className="w-12 h-12 mb-4 text-gray-300" />
                                                    <p className="mb-2 text-lg font-medium text-gray-900">
                                                        Tidak ada data karyawan
                                                    </p>
                                                    <p className="text-gray-500">
                                                        {searchTerm ||
                                                        statusFilter !==
                                                            "all" ||
                                                        unitFilter !== "all" ||
                                                        genderFilter !== "all"
                                                            ? "Tidak ada karyawan yang sesuai dengan filter pencarian."
                                                            : "Belum ada data karyawan yang ditambahkan."}
                                                    </p>
                                                    {(searchTerm ||
                                                        statusFilter !==
                                                            "all" ||
                                                        unitFilter !== "all" ||
                                                        genderFilter !==
                                                            "all") && (
                                                        <button
                                                            onClick={
                                                                clearFilters
                                                            }
                                                            className="mt-4 px-4 py-2 text-sm text-[#439454] border border-[#439454] rounded-lg hover:bg-[#439454] hover:text-white transition-colors duration-200"
                                                        >
                                                            Hapus Semua Filter
                                                        </button>
                                                    )}
                                                </div>
                                            </td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {/* Employee Detail Modal */}
                {showEmployeeModal && selectedEmployee && (
                    <div className="fixed inset-0 z-50 overflow-y-auto">
                        <div className="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                            <div
                                className="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"
                                onClick={closeModal}
                            ></div>

                            <div className="inline-block w-full max-w-4xl px-6 py-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white rounded-lg shadow-xl">
                                {/* Modal Header */}
                                <div className="flex items-center justify-between pb-4 border-b border-gray-200">
                                    <div className="flex items-center gap-4">
                                        <div className="h-16 w-16 rounded-full bg-[#439454] flex items-center justify-center">
                                            <span className="text-xl font-bold text-white">
                                                {getInitials(
                                                    selectedEmployee.nama_lengkap
                                                )}
                                            </span>
                                        </div>
                                        <div>
                                            <h3 className="text-xl font-semibold text-gray-900">
                                                {selectedEmployee.nama_lengkap}
                                            </h3>
                                            <p className="text-sm text-gray-500">
                                                NIP: {selectedEmployee.nip}
                                            </p>
                                        </div>
                                    </div>
                                    <button
                                        onClick={closeModal}
                                        className="p-2 text-gray-400 transition-colors duration-200 rounded-lg hover:text-gray-600 hover:bg-gray-100"
                                    >
                                        <X className="w-6 h-6" />
                                    </button>
                                </div>

                                {/* Modal Content */}
                                <div className="mt-6 space-y-6">
                                    <div className="grid grid-cols-1 gap-6 lg:grid-cols-2">
                                        {/* Personal Information */}
                                        <div className="space-y-4">
                                            <h3 className="pb-2 text-lg font-semibold text-gray-900 border-b border-gray-200">
                                                Informasi Pribadi
                                            </h3>

                                            <div className="space-y-3">
                                                <div className="flex items-center gap-3">
                                                    <User className="w-5 h-5 text-gray-500" />
                                                    <div>
                                                        <p className="text-sm text-gray-500">
                                                            Jenis Kelamin
                                                        </p>
                                                        <p className="font-medium">
                                                            {
                                                                selectedEmployee.jenis_kelamin
                                                            }
                                                        </p>
                                                    </div>
                                                </div>

                                                <div className="flex items-center gap-3">
                                                    <MapPin className="w-5 h-5 text-gray-500" />
                                                    <div>
                                                        <p className="text-sm text-gray-500">
                                                            Tempat, Tanggal
                                                            Lahir
                                                        </p>
                                                        <p className="font-medium">
                                                            {selectedEmployee.tempat_lahir
                                                                ? `${
                                                                      selectedEmployee.tempat_lahir
                                                                  }, ${formatDate(
                                                                      selectedEmployee.tanggal_lahir
                                                                  )}`
                                                                : "-"}
                                                        </p>
                                                    </div>
                                                </div>

                                                <div className="flex items-center gap-3">
                                                    <Calendar className="w-5 h-5 text-gray-500" />
                                                    <div>
                                                        <p className="text-sm text-gray-500">
                                                            Usia
                                                        </p>
                                                        <p className="font-medium">
                                                            {selectedEmployee.usia
                                                                ? `${selectedEmployee.usia} tahun`
                                                                : "-"}
                                                        </p>
                                                    </div>
                                                </div>

                                                <div className="flex items-center gap-3">
                                                    <Phone className="w-5 h-5 text-gray-500" />
                                                    <div>
                                                        <p className="text-sm text-gray-500">
                                                            No. Handphone
                                                        </p>
                                                        <p className="font-medium">
                                                            {selectedEmployee.handphone ||
                                                                "-"}
                                                        </p>
                                                    </div>
                                                </div>

                                                <div className="flex items-center gap-3">
                                                    <Mail className="w-5 h-5 text-gray-500" />
                                                    <div>
                                                        <p className="text-sm text-gray-500">
                                                            Email
                                                        </p>
                                                        <p className="font-medium">
                                                            {selectedEmployee.email ||
                                                                "-"}
                                                        </p>
                                                    </div>
                                                </div>

                                                <div className="flex items-start gap-3">
                                                    <MapPin className="w-5 h-5 mt-1 text-gray-500" />
                                                    <div>
                                                        <p className="text-sm text-gray-500">
                                                            Alamat
                                                        </p>
                                                        <p className="font-medium">
                                                            {selectedEmployee.alamat ||
                                                                "-"}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {/* Work Information */}
                                        <div className="space-y-4">
                                            <h3 className="pb-2 text-lg font-semibold text-gray-900 border-b border-gray-200">
                                                Informasi Pekerjaan
                                            </h3>

                                            <div className="space-y-3">
                                                <div className="flex items-center gap-3">
                                                    <Building className="w-5 h-5 text-gray-500" />
                                                    <div>
                                                        <p className="text-sm text-gray-500">
                                                            Unit Organisasi
                                                        </p>
                                                        <p className="font-medium">
                                                            {
                                                                selectedEmployee.unit_organisasi
                                                            }
                                                        </p>
                                                    </div>
                                                </div>

                                                <div className="flex items-center gap-3">
                                                    <Badge className="w-5 h-5 text-gray-500" />
                                                    <div>
                                                        <p className="text-sm text-gray-500">
                                                            Jabatan
                                                        </p>
                                                        <p className="font-medium">
                                                            {selectedEmployee.jabatan ||
                                                                selectedEmployee.nama_jabatan}
                                                        </p>
                                                    </div>
                                                </div>

                                                <div className="flex items-center gap-3">
                                                    <Calendar className="w-5 h-5 text-gray-500" />
                                                    <div>
                                                        <p className="text-sm text-gray-500">
                                                            TMT Mulai Jabatan
                                                        </p>
                                                        <p className="font-medium">
                                                            {formatDate(
                                                                selectedEmployee.tmt_mulai_jabatan
                                                            )}
                                                        </p>
                                                    </div>
                                                </div>

                                                <div className="flex items-center gap-3">
                                                    <Calendar className="w-5 h-5 text-gray-500" />
                                                    <div>
                                                        <p className="text-sm text-gray-500">
                                                            TMT Mulai Kerja
                                                        </p>
                                                        <p className="font-medium">
                                                            {formatDate(
                                                                selectedEmployee.tmt_mulai_kerja
                                                            )}
                                                        </p>
                                                    </div>
                                                </div>

                                                <div className="flex items-center gap-3">
                                                    <Users className="w-5 h-5 text-gray-500" />
                                                    <div>
                                                        <p className="text-sm text-gray-500">
                                                            Status Pegawai
                                                        </p>
                                                        <span
                                                            className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${
                                                                selectedEmployee.status_pegawai ===
                                                                "PEGAWAI TETAP"
                                                                    ? "bg-green-100 text-green-800"
                                                                    : "bg-yellow-100 text-yellow-800"
                                                            }`}
                                                        >
                                                            {
                                                                selectedEmployee.status_pegawai
                                                            }
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {/* Additional Information */}
                                    <div className="pt-4 border-t border-gray-200">
                                        <h3 className="mb-4 text-lg font-semibold text-gray-900">
                                            Informasi Tambahan
                                        </h3>
                                        <div className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                                            <div className="p-4 rounded-lg bg-gray-50">
                                                <p className="text-sm font-medium text-gray-500">
                                                    Pendidikan Terakhir
                                                </p>
                                                <p className="text-sm text-gray-900">
                                                    {selectedEmployee.pendidikan_terakhir ||
                                                        selectedEmployee.pendidikan ||
                                                        "-"}
                                                </p>
                                            </div>
                                            <div className="p-4 rounded-lg bg-gray-50">
                                                <p className="text-sm font-medium text-gray-500">
                                                    Instansi Pendidikan
                                                </p>
                                                <p className="text-sm text-gray-900">
                                                    {selectedEmployee.instansi_pendidikan ||
                                                        "-"}
                                                </p>
                                            </div>
                                            <div className="p-4 rounded-lg bg-gray-50">
                                                <p className="text-sm font-medium text-gray-500">
                                                    Jurusan
                                                </p>
                                                <p className="text-sm text-gray-900">
                                                    {selectedEmployee.jurusan ||
                                                        "-"}
                                                </p>
                                            </div>
                                            <div className="p-4 rounded-lg bg-gray-50">
                                                <p className="text-sm font-medium text-gray-500">
                                                    Tahun Lulus
                                                </p>
                                                <p className="text-sm text-gray-900">
                                                    {selectedEmployee.tahun_lulus ||
                                                        "-"}
                                                </p>
                                            </div>
                                            <div className="p-4 rounded-lg bg-gray-50">
                                                <p className="text-sm font-medium text-gray-500">
                                                    Jenis Sepatu
                                                </p>
                                                <p className="text-sm text-gray-900">
                                                    {selectedEmployee.jenis_sepatu ||
                                                        "-"}
                                                </p>
                                            </div>
                                            <div className="p-4 rounded-lg bg-gray-50">
                                                <p className="text-sm font-medium text-gray-500">
                                                    Ukuran Sepatu
                                                </p>
                                                <p className="text-sm text-gray-900">
                                                    {selectedEmployee.ukuran_sepatu ||
                                                        "-"}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {/* Modal Footer */}
                                <div className="flex justify-end gap-3 pt-6 mt-6 border-t border-gray-200">
                                    <button
                                        onClick={closeModal}
                                        className="px-4 py-2 text-sm font-medium text-gray-700 transition-colors duration-200 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
                                    >
                                        Tutup
                                    </button>
                                    <Link
                                        href={route(
                                            "employees.edit",
                                            selectedEmployee.id
                                        )}
                                        className="inline-flex items-center gap-2 px-4 py-2 bg-[#439454] text-white rounded-lg text-sm font-medium hover:bg-[#3a7d46] transition-colors duration-200"
                                    >
                                        <Edit2 className="w-4 h-4" />
                                        Edit Data
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                )}
            </div>
        </DashboardLayout>
    );
}
