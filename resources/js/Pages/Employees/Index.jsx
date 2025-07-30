import React, { useState, useEffect } from "react";
import { Head, Link, router } from "@inertiajs/react";
import {
    Eye,
    Search,
    Filter,
    Plus,
    Download,
    Upload,
    Edit,
    Trash2,
    User,
    Calendar,
    Building,
    Badge,
    Phone,
    Mail,
    MapPin,
    GraduationCap,
} from "lucide-react";

const EmployeeManagement = ({
    employees = [],
    organizations = [],
    filters = {},
}) => {
    const [searchTerm, setSearchTerm] = useState(filters.search || "");
    const [statusFilter, setStatusFilter] = useState(
        filters.status_pegawai || "all"
    );
    const [unitFilter, setUnitFilter] = useState(
        filters.unit_organisasi || "all"
    );
    const [showDetailModal, setShowDetailModal] = useState(false);
    const [selectedEmployee, setSelectedEmployee] = useState(null);
    const [isLoading, setIsLoading] = useState(false);

    // Handle search with debounce
    useEffect(() => {
        const timeoutId = setTimeout(() => {
            router.get(
                route("employees.index"),
                {
                    search: searchTerm,
                    status_pegawai: statusFilter,
                    unit_organisasi: unitFilter,
                },
                {
                    preserveState: true,
                    preserveScroll: true,
                }
            );
        }, 300);

        return () => clearTimeout(timeoutId);
    }, [searchTerm, statusFilter, unitFilter]);

    // Handle view employee detail
    const handleViewDetail = (employee) => {
        setSelectedEmployee(employee);
        setShowDetailModal(true);
    };

    // Close detail modal
    const closeDetailModal = () => {
        setShowDetailModal(false);
        setSelectedEmployee(null);
    };

    // Format date for display
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

    // Get unique units for filter
    const uniqueUnits = [
        ...new Set(employees.map((emp) => emp.unit_organisasi)),
    ].filter(Boolean);

    return (
        <>
            <Head title="Management Karyawan - GAPURA ANGKASA SDM" />

            <div className="min-h-screen bg-white">
                {/* Header Section */}
                <div className="px-6 py-4 bg-white border-b border-gray-200">
                    <div className="flex flex-col md:flex-row md:items-center md:justify-between">
                        <div>
                            <h1 className="text-2xl font-bold text-gray-900">
                                Management Karyawan
                            </h1>
                            <p className="mt-1 text-sm text-gray-600">
                                Kelola data karyawan PT Gapura Angkasa - Bandar
                                Udara Ngurah Rai
                            </p>
                        </div>
                        <div className="flex gap-3 mt-4 md:mt-0">
                            <Link
                                href={route("employees.create")}
                                className="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 transition-colors duration-200 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
                            >
                                <Upload className="w-4 h-4" />
                                Import Data
                            </Link>
                            <Link
                                href={route("employees.create")}
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

                {/* Filters Section */}
                <div className="px-6 py-4 bg-white border-b border-gray-200">
                    <div className="flex flex-col gap-4 md:flex-row">
                        {/* Search Input */}
                        <div className="relative flex-1">
                            <Search className="absolute w-5 h-5 text-gray-400 transform -translate-y-1/2 left-3 top-1/2" />
                            <input
                                type="text"
                                placeholder="Cari berdasarkan NIP, nama, atau jabatan..."
                                value={searchTerm}
                                onChange={(e) => setSearchTerm(e.target.value)}
                                className="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#439454] focus:border-[#439454] transition-colors duration-200"
                            />
                        </div>

                        {/* Status Filter */}
                        <div className="relative">
                            <select
                                value={statusFilter}
                                onChange={(e) =>
                                    setStatusFilter(e.target.value)
                                }
                                className="appearance-none bg-white border border-gray-300 rounded-lg px-4 py-2 pr-8 focus:ring-2 focus:ring-[#439454] focus:border-[#439454] transition-colors duration-200"
                            >
                                <option value="all">Semua Status</option>
                                <option value="PEGAWAI TETAP">
                                    Pegawai Tetap
                                </option>
                                <option value="TAD">TAD</option>
                            </select>
                        </div>

                        {/* Unit Filter */}
                        <div className="relative">
                            <select
                                value={unitFilter}
                                onChange={(e) => setUnitFilter(e.target.value)}
                                className="appearance-none bg-white border border-gray-300 rounded-lg px-4 py-2 pr-8 focus:ring-2 focus:ring-[#439454] focus:border-[#439454] transition-colors duration-200"
                            >
                                <option value="all">Semua Unit</option>
                                {uniqueUnits.map((unit) => (
                                    <option key={unit} value={unit}>
                                        {unit}
                                    </option>
                                ))}
                            </select>
                        </div>
                    </div>
                </div>

                {/* Statistics Cards */}
                <div className="px-6 py-4">
                    <div className="grid grid-cols-1 gap-4 md:grid-cols-4">
                        <div className="p-4 bg-white border border-gray-200 rounded-lg">
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
                        <div className="p-4 bg-white border border-gray-200 rounded-lg">
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
                        <div className="p-4 bg-white border border-gray-200 rounded-lg">
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
                        <div className="p-4 bg-white border border-gray-200 rounded-lg">
                            <div className="flex items-center justify-between">
                                <div>
                                    <p className="text-sm font-medium text-gray-600">
                                        Unit Organisasi
                                    </p>
                                    <p className="text-2xl font-bold text-gray-900">
                                        {uniqueUnits.length}
                                    </p>
                                </div>
                                <div className="p-3 bg-purple-100 rounded-lg">
                                    <Building className="w-6 h-6 text-purple-600" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Employee Table */}
                <div className="px-6 pb-6">
                    <div className="overflow-hidden bg-white border border-gray-200 rounded-lg">
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
                                                                    {employee.nama_lengkap.charAt(
                                                                        0
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
                                                                {
                                                                    employee.nama_jabatan
                                                                }
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
                                                    <div className="flex gap-2">
                                                        <button
                                                            onClick={() =>
                                                                handleViewDetail(
                                                                    employee
                                                                )
                                                            }
                                                            className="text-[#439454] hover:text-[#3a7d46] transition-colors duration-200 p-1 rounded"
                                                            title="Lihat Detail"
                                                        >
                                                            <Eye className="w-4 h-4" />
                                                        </button>
                                                        <Link
                                                            href={route(
                                                                "employees.edit",
                                                                employee.id
                                                            )}
                                                            className="p-1 text-blue-600 transition-colors duration-200 rounded hover:text-blue-900"
                                                            title="Edit"
                                                        >
                                                            <Edit className="w-4 h-4" />
                                                        </Link>
                                                        <button
                                                            onClick={() => {
                                                                if (
                                                                    confirm(
                                                                        "Apakah Anda yakin ingin menghapus karyawan ini?"
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
                                                className="px-6 py-8 text-center text-gray-500"
                                            >
                                                <div className="flex flex-col items-center">
                                                    <User className="w-12 h-12 mb-4 text-gray-300" />
                                                    <p className="text-lg font-medium">
                                                        Tidak ada data karyawan
                                                    </p>
                                                    <p className="text-sm">
                                                        Silakan tambah karyawan
                                                        baru atau ubah filter
                                                        pencarian
                                                    </p>
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
                {showDetailModal && selectedEmployee && (
                    <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-50">
                        <div className="bg-white rounded-lg max-w-4xl w-full max-h-[90vh] overflow-y-auto">
                            {/* Modal Header */}
                            <div className="flex items-center justify-between p-6 border-b border-gray-200">
                                <h2 className="text-xl font-bold text-gray-900">
                                    Detail Karyawan
                                </h2>
                                <button
                                    onClick={closeDetailModal}
                                    className="text-gray-400 transition-colors duration-200 hover:text-gray-600"
                                >
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
                                            d="M6 18L18 6M6 6l12 12"
                                        />
                                    </svg>
                                </button>
                            </div>

                            {/* Modal Content */}
                            <div className="p-6">
                                <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                                    {/* Personal Information */}
                                    <div className="space-y-4">
                                        <h3 className="pb-2 text-lg font-semibold text-gray-900 border-b border-gray-200">
                                            Informasi Personal
                                        </h3>

                                        <div className="space-y-3">
                                            <div className="flex items-center gap-3">
                                                <Badge className="w-5 h-5 text-gray-500" />
                                                <div>
                                                    <p className="text-sm text-gray-500">
                                                        NIP
                                                    </p>
                                                    <p className="font-medium">
                                                        {selectedEmployee.nip}
                                                    </p>
                                                </div>
                                            </div>

                                            <div className="flex items-center gap-3">
                                                <User className="w-5 h-5 text-gray-500" />
                                                <div>
                                                    <p className="text-sm text-gray-500">
                                                        Nama Lengkap
                                                    </p>
                                                    <p className="font-medium">
                                                        {
                                                            selectedEmployee.nama_lengkap
                                                        }
                                                    </p>
                                                </div>
                                            </div>

                                            <div className="flex items-center gap-3">
                                                <Calendar className="w-5 h-5 text-gray-500" />
                                                <div>
                                                    <p className="text-sm text-gray-500">
                                                        Tanggal Lahir
                                                    </p>
                                                    <p className="font-medium">
                                                        {
                                                            selectedEmployee.tempat_lahir
                                                        }
                                                        ,{" "}
                                                        {formatDate(
                                                            selectedEmployee.tanggal_lahir
                                                        )}
                                                    </p>
                                                </div>
                                            </div>

                                            <div className="flex items-center gap-3">
                                                <User className="w-5 h-5 text-gray-500" />
                                                <div>
                                                    <p className="text-sm text-gray-500">
                                                        Jenis Kelamin
                                                    </p>
                                                    <p className="font-medium">
                                                        {selectedEmployee.jenis_kelamin ===
                                                        "L"
                                                            ? "Laki-laki"
                                                            : "Perempuan"}
                                                    </p>
                                                </div>
                                            </div>

                                            <div className="flex items-center gap-3">
                                                <Phone className="w-5 h-5 text-gray-500" />
                                                <div>
                                                    <p className="text-sm text-gray-500">
                                                        Nomor Handphone
                                                    </p>
                                                    <p className="font-medium">
                                                        {selectedEmployee.handphone ||
                                                            "-"}
                                                    </p>
                                                </div>
                                            </div>

                                            <div className="flex items-center gap-3">
                                                <MapPin className="w-5 h-5 text-gray-500" />
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

                                            <div className="flex items-center gap-3">
                                                <GraduationCap className="w-5 h-5 text-gray-500" />
                                                <div>
                                                    <p className="text-sm text-gray-500">
                                                        Pendidikan
                                                    </p>
                                                    <p className="font-medium">
                                                        {
                                                            selectedEmployee.pendidikan
                                                        }{" "}
                                                        -{" "}
                                                        {
                                                            selectedEmployee.jurusan
                                                        }
                                                    </p>
                                                    <p className="text-sm text-gray-500">
                                                        {
                                                            selectedEmployee.instansi_pendidikan
                                                        }{" "}
                                                        (
                                                        {
                                                            selectedEmployee.tahun_lulus
                                                        }
                                                        )
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
                                                        Nama Jabatan
                                                    </p>
                                                    <p className="font-medium">
                                                        {
                                                            selectedEmployee.nama_jabatan
                                                        }
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
                                                <Badge className="w-5 h-5 text-gray-500" />
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

                                            <div className="flex items-center gap-3">
                                                <Calendar className="w-5 h-5 text-gray-500" />
                                                <div>
                                                    <p className="text-sm text-gray-500">
                                                        Masa Kerja
                                                    </p>
                                                    <p className="font-medium">
                                                        {selectedEmployee.masa_kerja_tahun ||
                                                            "-"}{" "}
                                                        (
                                                        {selectedEmployee.masa_kerja_bulan ||
                                                            "-"}
                                                        )
                                                    </p>
                                                </div>
                                            </div>

                                            {selectedEmployee.tmt_pensiun && (
                                                <div className="flex items-center gap-3">
                                                    <Calendar className="w-5 h-5 text-gray-500" />
                                                    <div>
                                                        <p className="text-sm text-gray-500">
                                                            TMT Pensiun
                                                        </p>
                                                        <p className="font-medium">
                                                            {formatDate(
                                                                selectedEmployee.tmt_pensiun
                                                            )}
                                                        </p>
                                                    </div>
                                                </div>
                                            )}

                                            <div className="flex items-center gap-3">
                                                <Badge className="w-5 h-5 text-gray-500" />
                                                <div>
                                                    <p className="text-sm text-gray-500">
                                                        Kelompok Jabatan
                                                    </p>
                                                    <p className="font-medium">
                                                        {selectedEmployee.kelompok_jabatan ||
                                                            "-"}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {/* Additional Information */}
                                    <div className="space-y-4 md:col-span-2">
                                        <h3 className="pb-2 text-lg font-semibold text-gray-900 border-b border-gray-200">
                                            Informasi Tambahan
                                        </h3>

                                        <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
                                            <div>
                                                <p className="text-sm text-gray-500">
                                                    Jenis Sepatu
                                                </p>
                                                <p className="font-medium">
                                                    {selectedEmployee.jenis_sepatu ||
                                                        "-"}
                                                </p>
                                            </div>
                                            <div>
                                                <p className="text-sm text-gray-500">
                                                    Ukuran Sepatu
                                                </p>
                                                <p className="font-medium">
                                                    {selectedEmployee.ukuran_sepatu ||
                                                        "-"}
                                                </p>
                                            </div>
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
                                            <div>
                                                <p className="text-sm text-gray-500">
                                                    Tinggi Badan
                                                </p>
                                                <p className="font-medium">
                                                    {selectedEmployee.height
                                                        ? `${selectedEmployee.height} cm`
                                                        : "-"}
                                                </p>
                                            </div>
                                            <div>
                                                <p className="text-sm text-gray-500">
                                                    Berat Badan
                                                </p>
                                                <p className="font-medium">
                                                    {selectedEmployee.weight
                                                        ? `${selectedEmployee.weight} kg`
                                                        : "-"}
                                                </p>
                                            </div>
                                            <div>
                                                <p className="text-sm text-gray-500">
                                                    Kota Domisili
                                                </p>
                                                <p className="font-medium">
                                                    {selectedEmployee.kota_domisili ||
                                                        "-"}
                                                </p>
                                            </div>
                                        </div>

                                        {/* BPJS Information */}
                                        <div className="grid grid-cols-1 gap-4 mt-4 md:grid-cols-2">
                                            <div>
                                                <p className="text-sm text-gray-500">
                                                    No. BPJS Kesehatan
                                                </p>
                                                <p className="font-medium">
                                                    {selectedEmployee.no_bpjs_kesehatan ||
                                                        "-"}
                                                </p>
                                            </div>
                                            <div>
                                                <p className="text-sm text-gray-500">
                                                    No. BPJS Ketenagakerjaan
                                                </p>
                                                <p className="font-medium">
                                                    {selectedEmployee.no_bpjs_ketenagakerjaan ||
                                                        "-"}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {/* Modal Footer */}
                            <div className="flex items-center justify-end gap-3 p-6 border-t border-gray-200">
                                <button
                                    onClick={closeDetailModal}
                                    className="px-4 py-2 text-sm font-medium text-gray-700 transition-colors duration-200 border border-gray-300 rounded-lg hover:bg-gray-50"
                                >
                                    Tutup
                                </button>
                                <Link
                                    href={route(
                                        "employees.edit",
                                        selectedEmployee.id
                                    )}
                                    className="px-4 py-2 bg-[#439454] text-white rounded-lg text-sm font-medium hover:bg-[#3a7d46] transition-colors duration-200"
                                >
                                    Edit Karyawan
                                </Link>
                            </div>
                        </div>
                    </div>
                )}
            </div>

            {/* Custom Styles */}
            <style jsx>{`
                /* Custom scrollbar for modal */
                .overflow-y-auto::-webkit-scrollbar {
                    width: 6px;
                }

                .overflow-y-auto::-webkit-scrollbar-track {
                    background: #f1f1f1;
                    border-radius: 6px;
                }

                .overflow-y-auto::-webkit-scrollbar-thumb {
                    background: #c1c1c1;
                    border-radius: 6px;
                }

                .overflow-y-auto::-webkit-scrollbar-thumb:hover {
                    background: #a8a8a8;
                }

                /* Hover effects for table rows */
                tbody tr:hover {
                    transform: translateY(-1px);
                    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                }

                /* Custom select arrow */
                select {
                    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
                    background-position: right 0.5rem center;
                    background-repeat: no-repeat;
                    background-size: 1.5em 1.5em;
                }

                /* Button hover effects */
                .hover\\:bg-\\[\\#439454\\]:hover {
                    background-color: #439454;
                }

                .hover\\:bg-\\[\\#3a7d46\\]:hover {
                    background-color: #3a7d46;
                }

                /* Focus ring color */
                .focus\\:ring-\\[\\#439454\\]:focus {
                    --tw-ring-color: #439454;
                }

                .focus\\:border-\\[\\#439454\\]:focus {
                    --tw-border-opacity: 1;
                    border-color: #439454;
                }
            `}</style>
        </>
    );
};

export default EmployeeManagement;
