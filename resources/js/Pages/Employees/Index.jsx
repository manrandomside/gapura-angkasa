import React, { useState, useEffect, useMemo } from "react";
import { Head, Link, router } from "@inertiajs/react";
import DashboardLayout from "@/Layouts/DashboardLayout";
import EmployeeDetailModal from "@/Components/EmployeeDetailModal";
import {
    Search,
    Plus,
    FileDown,
    FileUp,
    Eye,
    Edit,
    Trash2,
    X,
    Filter,
    Users,
    UserCheck,
    Calendar,
    Building2,
} from "lucide-react";

export default function Index({ employees: initialEmployees = [], auth }) {
    // State management
    const [employees, setEmployees] = useState(initialEmployees || []);
    const [searchQuery, setSearchQuery] = useState("");
    const [statusFilter, setStatusFilter] = useState("all");
    const [unitFilter, setUnitFilter] = useState("all");
    const [genderFilter, setGenderFilter] = useState("all");
    const [shoeTypeFilter, setShoeTypeFilter] = useState("all");
    const [shoeSizeFilter, setShoeSizeFilter] = useState("all");
    const [showFilters, setShowFilters] = useState(false);
    const [showEmployeeModal, setShowEmployeeModal] = useState(false);
    const [selectedEmployee, setSelectedEmployee] = useState(null);
    const [loading, setLoading] = useState(false);

    // Get unique values for filters
    const getUniqueUnits = () => {
        const units = [
            ...new Set(
                employees.map((emp) => emp.unit_organisasi).filter(Boolean)
            ),
        ];
        return units.sort();
    };

    const getUniqueShoeTypes = () => {
        const types = [
            ...new Set(
                employees.map((emp) => emp.jenis_sepatu).filter(Boolean)
            ),
        ];
        return types.sort();
    };

    const getUniqueShoeSizes = () => {
        const sizes = [
            ...new Set(
                employees.map((emp) => emp.ukuran_sepatu).filter(Boolean)
            ),
        ];
        return sizes.sort((a, b) => parseInt(a) - parseInt(b));
    };

    // Filter employees based on all active filters
    const filteredEmployees = useMemo(() => {
        let filtered = employees;

        // Search filter
        if (searchQuery.trim()) {
            const query = searchQuery.toLowerCase();
            filtered = filtered.filter(
                (emp) =>
                    emp.nama_lengkap?.toLowerCase().includes(query) ||
                    emp.nip?.toLowerCase().includes(query) ||
                    emp.nama_jabatan?.toLowerCase().includes(query) ||
                    emp.jabatan?.toLowerCase().includes(query) ||
                    emp.unit_organisasi?.toLowerCase().includes(query) ||
                    emp.jenis_sepatu?.toLowerCase().includes(query) ||
                    emp.ukuran_sepatu?.toLowerCase().includes(query) ||
                    emp.nama_organisasi?.toLowerCase().includes(query) ||
                    emp.kota_domisili?.toLowerCase().includes(query) ||
                    emp.instansi_pendidikan?.toLowerCase().includes(query)
            );
        }

        // Status filter
        if (statusFilter !== "all") {
            filtered = filtered.filter(
                (emp) => emp.status_pegawai === statusFilter
            );
        }

        // Unit filter
        if (unitFilter !== "all") {
            filtered = filtered.filter(
                (emp) => emp.unit_organisasi === unitFilter
            );
        }

        // Gender filter
        if (genderFilter !== "all") {
            filtered = filtered.filter(
                (emp) => emp.jenis_kelamin === genderFilter
            );
        }

        // Shoe type filter
        if (shoeTypeFilter !== "all") {
            filtered = filtered.filter(
                (emp) => emp.jenis_sepatu === shoeTypeFilter
            );
        }

        // Shoe size filter
        if (shoeSizeFilter !== "all") {
            filtered = filtered.filter(
                (emp) => emp.ukuran_sepatu === shoeSizeFilter
            );
        }

        return filtered;
    }, [
        employees,
        searchQuery,
        statusFilter,
        unitFilter,
        genderFilter,
        shoeTypeFilter,
        shoeSizeFilter,
    ]);

    // Handle filter changes
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
            case "shoeType":
                setShoeTypeFilter(value);
                break;
            case "shoeSize":
                setShoeSizeFilter(value);
                break;
        }
    };

    // Remove specific filter
    const removeFilter = (filterType) => {
        handleFilterChange(filterType, "all");
    };

    // Clear all filters
    const clearAllFilters = () => {
        setSearchQuery("");
        setStatusFilter("all");
        setUnitFilter("all");
        setGenderFilter("all");
        setShoeTypeFilter("all");
        setShoeSizeFilter("all");
    };

    // Check if any filters are active
    const hasActiveFilters = () => {
        return (
            searchQuery.trim() ||
            statusFilter !== "all" ||
            unitFilter !== "all" ||
            genderFilter !== "all" ||
            shoeTypeFilter !== "all" ||
            shoeSizeFilter !== "all"
        );
    };

    // Search employees
    const handleSearch = () => {
        // Search is handled by the filteredEmployees useMemo
        console.log("Searching with filters:", {
            searchQuery,
            statusFilter,
            unitFilter,
            genderFilter,
            shoeTypeFilter,
            shoeSizeFilter,
        });
    };

    // Get employee initials for avatar
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

    // Statistics calculations
    const stats = useMemo(() => {
        const total = employees.length;
        const pegawaiTetap = employees.filter(
            (emp) => emp.status_pegawai === "PEGAWAI TETAP"
        ).length;
        const tad = employees.filter(
            (emp) => emp.status_pegawai === "TAD"
        ).length;
        const uniqueUnits = new Set(
            employees.map((emp) => emp.unit_organisasi).filter(Boolean)
        ).size;

        return { total, pegawaiTetap, tad, uniqueUnits };
    }, [employees]);

    return (
        <DashboardLayout title="Management Karyawan">
            <Head title="Management Karyawan - GAPURA ANGKASA SDM" />

            <div className="min-h-screen bg-gray-50">
                {/* Header Section */}
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
                                {/* Display data count indicator */}
                                <p className="mt-1 text-xs font-medium text-green-600">
                                    Data Source: SDM Employee Seeder (
                                    {employees.length} karyawan valid)
                                </p>
                            </div>
                            <div className="flex gap-3 mt-4 md:mt-0">
                                <Link
                                    href={route("employees.import")}
                                    className="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:border-[#439454] transition-colors duration-200"
                                >
                                    <FileUp className="w-4 h-4" />
                                    Import Data
                                </Link>
                                <Link
                                    href={route("employees.export")}
                                    className="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:border-[#439454] transition-colors duration-200"
                                >
                                    <FileDown className="w-4 h-4" />
                                    Export Data
                                </Link>
                                <Link
                                    href={route("employees.create")}
                                    className="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-[#439454] rounded-lg hover:bg-[#367a41] transition-colors duration-200 shadow-sm"
                                >
                                    <Plus className="w-4 h-4" />
                                    Tambah Karyawan
                                </Link>
                            </div>
                        </div>

                        {/* Statistics Cards */}
                        <div className="grid grid-cols-1 gap-4 mt-6 sm:grid-cols-2 lg:grid-cols-4">
                            <div className="p-4 border border-blue-200 rounded-lg bg-blue-50">
                                <div className="flex items-center">
                                    <div className="p-2 bg-blue-100 rounded-lg">
                                        <Users className="w-5 h-5 text-blue-600" />
                                    </div>
                                    <div className="ml-3">
                                        <p className="text-sm font-medium text-blue-900">
                                            Total Karyawan
                                        </p>
                                        <p className="text-2xl font-bold text-blue-600">
                                            {stats.total}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div className="p-4 border border-green-200 rounded-lg bg-green-50">
                                <div className="flex items-center">
                                    <div className="p-2 bg-green-100 rounded-lg">
                                        <UserCheck className="w-5 h-5 text-green-600" />
                                    </div>
                                    <div className="ml-3">
                                        <p className="text-sm font-medium text-green-900">
                                            Pegawai Tetap
                                        </p>
                                        <p className="text-2xl font-bold text-green-600">
                                            {stats.pegawaiTetap}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div className="p-4 border border-yellow-200 rounded-lg bg-yellow-50">
                                <div className="flex items-center">
                                    <div className="p-2 bg-yellow-100 rounded-lg">
                                        <Calendar className="w-5 h-5 text-yellow-600" />
                                    </div>
                                    <div className="ml-3">
                                        <p className="text-sm font-medium text-yellow-900">
                                            TAD
                                        </p>
                                        <p className="text-2xl font-bold text-yellow-600">
                                            {stats.tad}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div className="p-4 border border-purple-200 rounded-lg bg-purple-50">
                                <div className="flex items-center">
                                    <div className="p-2 bg-purple-100 rounded-lg">
                                        <Building2 className="w-5 h-5 text-purple-600" />
                                    </div>
                                    <div className="ml-3">
                                        <p className="text-sm font-medium text-purple-900">
                                            Unit Organisasi
                                        </p>
                                        <p className="text-2xl font-bold text-purple-600">
                                            {stats.uniqueUnits}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Search and Filter Section */}
                <div className="px-6 py-6 bg-white border-b border-gray-200">
                    {/* Search Bar */}
                    <div className="flex gap-4 mb-4">
                        <div className="flex-1">
                            <div className="relative">
                                <Search className="absolute w-4 h-4 text-gray-400 transform -translate-y-1/2 left-3 top-1/2" />
                                <input
                                    type="text"
                                    placeholder="Cari berdasarkan NIP, nama, jabatan, unit organisasi, instansi pendidikan..."
                                    value={searchQuery}
                                    onChange={(e) =>
                                        setSearchQuery(e.target.value)
                                    }
                                    className="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#439454] focus:border-transparent"
                                />
                            </div>
                        </div>
                        <button
                            onClick={() => setShowFilters(!showFilters)}
                            className={`inline-flex items-center gap-2 px-4 py-3 text-sm font-medium border rounded-lg transition-colors duration-200 ${
                                showFilters || hasActiveFilters()
                                    ? "text-white bg-[#439454] border-[#439454]"
                                    : "text-gray-700 bg-white border-gray-300 hover:bg-gray-50 hover:border-[#439454]"
                            }`}
                        >
                            <Filter className="w-4 h-4" />
                            Filter
                        </button>
                        <button
                            onClick={handleSearch}
                            className="inline-flex items-center gap-2 px-6 py-3 text-sm font-medium text-white bg-[#439454] rounded-lg hover:bg-[#367a41] transition-colors duration-200 shadow-sm"
                        >
                            <Search className="w-4 h-4" />
                            Cari
                        </button>
                    </div>

                    {/* Advanced Filters */}
                    {showFilters && (
                        <div className="p-4 border border-gray-200 rounded-lg bg-gray-50">
                            <div className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-5">
                                {/* Status Pegawai Filter */}
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

                                {/* Unit Organisasi Filter */}
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

                                {/* Jenis Kelamin Filter */}
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
                                        <option value="L">Laki-laki</option>
                                        <option value="P">Perempuan</option>
                                    </select>
                                </div>

                                {/* Jenis Sepatu Filter */}
                                <div>
                                    <label className="block mb-2 text-sm font-medium text-gray-700">
                                        Jenis Sepatu
                                    </label>
                                    <select
                                        value={shoeTypeFilter}
                                        onChange={(e) =>
                                            handleFilterChange(
                                                "shoeType",
                                                e.target.value
                                            )
                                        }
                                        className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#439454] focus:border-transparent"
                                    >
                                        <option value="all">Semua Jenis</option>
                                        {getUniqueShoeTypes().map((type) => (
                                            <option key={type} value={type}>
                                                {type}
                                            </option>
                                        ))}
                                    </select>
                                </div>

                                {/* Ukuran Sepatu Filter */}
                                <div>
                                    <label className="block mb-2 text-sm font-medium text-gray-700">
                                        Ukuran Sepatu
                                    </label>
                                    <select
                                        value={shoeSizeFilter}
                                        onChange={(e) =>
                                            handleFilterChange(
                                                "shoeSize",
                                                e.target.value
                                            )
                                        }
                                        className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#439454] focus:border-transparent"
                                    >
                                        <option value="all">
                                            Semua Ukuran
                                        </option>
                                        {getUniqueShoeSizes().map((size) => (
                                            <option key={size} value={size}>
                                                {size}
                                            </option>
                                        ))}
                                    </select>
                                </div>
                            </div>

                            {/* Clear Filters Button */}
                            {hasActiveFilters() && (
                                <div className="mt-4 text-center">
                                    <button
                                        onClick={clearAllFilters}
                                        className="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-600 transition-colors duration-200 hover:text-gray-800"
                                    >
                                        <X className="w-4 h-4" />
                                        Reset Filter
                                    </button>
                                </div>
                            )}
                        </div>
                    )}

                    {/* Active Filters Display */}
                    {hasActiveFilters() && (
                        <div className="mt-4">
                            <div className="flex flex-wrap items-center gap-2">
                                <span className="text-sm font-medium text-gray-700">
                                    Filter aktif:
                                </span>
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
                                        Gender:{" "}
                                        {genderFilter === "L"
                                            ? "Laki-laki"
                                            : "Perempuan"}
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
                                {shoeTypeFilter !== "all" && (
                                    <span className="inline-flex items-center gap-1 px-3 py-1 text-xs font-medium text-blue-800 bg-blue-100 rounded-full">
                                        Sepatu: {shoeTypeFilter}
                                        <button
                                            onClick={() =>
                                                removeFilter("shoeType")
                                            }
                                            className="ml-1 text-blue-600 hover:text-blue-800"
                                        >
                                            <X className="w-3 h-3" />
                                        </button>
                                    </span>
                                )}
                                {shoeSizeFilter !== "all" && (
                                    <span className="inline-flex items-center gap-1 px-3 py-1 text-xs font-medium text-indigo-800 bg-indigo-100 rounded-full">
                                        Ukuran: {shoeSizeFilter}
                                        <button
                                            onClick={() =>
                                                removeFilter("shoeSize")
                                            }
                                            className="ml-1 text-indigo-600 hover:text-indigo-800"
                                        >
                                            <X className="w-3 h-3" />
                                        </button>
                                    </span>
                                )}
                            </div>

                            {/* Results Count */}
                            <div className="flex items-center mt-3 text-sm text-gray-600">
                                Menampilkan {filteredEmployees.length} dari{" "}
                                {employees.length} karyawan
                            </div>
                        </div>
                    )}
                </div>

                {/* Employee Table */}
                <div className="px-6 pb-6">
                    {filteredEmployees.length > 0 ? (
                        <div className="overflow-hidden bg-white border border-gray-200 rounded-lg shadow-sm">
                            <div className="overflow-x-auto">
                                <table className="min-w-full divide-y divide-gray-200">
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
                                                Unit Organisasi
                                            </th>
                                            <th className="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                                Jenis Sepatu
                                            </th>
                                            <th className="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                                Ukuran Sepatu
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
                                        {filteredEmployees.map(
                                            (employee, index) => (
                                                <tr
                                                    key={
                                                        employee.id ||
                                                        employee.nip ||
                                                        index
                                                    }
                                                    className="transition-colors duration-200 hover:bg-gray-50"
                                                >
                                                    <td className="px-6 py-4 text-sm font-medium text-gray-900 whitespace-nowrap">
                                                        {employee.nip || "-"}
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap">
                                                        <div className="flex items-center">
                                                            <div className="flex-shrink-0 w-8 h-8">
                                                                <div className="w-8 h-8 bg-[#439454] rounded-full flex items-center justify-center text-white text-xs font-medium">
                                                                    {getInitials(
                                                                        employee.nama_lengkap
                                                                    )}
                                                                </div>
                                                            </div>
                                                            <div className="ml-3">
                                                                <div className="text-sm font-medium text-gray-900">
                                                                    {employee.nama_lengkap ||
                                                                        "-"}
                                                                </div>
                                                                <div className="text-sm text-gray-500">
                                                                    {employee.nama_jabatan ||
                                                                        employee.jabatan ||
                                                                        "-"}
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
                                                            {employee.status_pegawai ||
                                                                "-"}
                                                        </span>
                                                    </td>
                                                    <td className="px-6 py-4 text-sm text-gray-900 whitespace-nowrap">
                                                        {employee.unit_organisasi ||
                                                            "-"}
                                                    </td>
                                                    <td className="px-6 py-4 text-sm text-gray-900 whitespace-nowrap">
                                                        <span
                                                            className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${
                                                                employee.jenis_sepatu ===
                                                                "Safety Shoes"
                                                                    ? "bg-red-100 text-red-800"
                                                                    : employee.jenis_sepatu ===
                                                                      "Pantofel"
                                                                    ? "bg-blue-100 text-blue-800"
                                                                    : "bg-gray-100 text-gray-800"
                                                            }`}
                                                        >
                                                            {employee.jenis_sepatu ||
                                                                "-"}
                                                        </span>
                                                    </td>
                                                    <td className="px-6 py-4 text-sm text-gray-900 whitespace-nowrap">
                                                        <span className="inline-flex px-2 py-1 text-xs font-semibold text-gray-800 bg-gray-100 rounded-full">
                                                            {employee.ukuran_sepatu ||
                                                                "-"}
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
                                                                className="p-1 text-blue-600 transition-colors duration-200 rounded hover:text-blue-900 hover:bg-blue-50"
                                                                title="Lihat Detail Lengkap"
                                                            >
                                                                <Eye className="w-4 h-4" />
                                                            </button>
                                                            <Link
                                                                href={route(
                                                                    "employees.edit",
                                                                    employee.id
                                                                )}
                                                                className="p-1 text-[#439454] transition-colors duration-200 rounded hover:text-[#367a41] hover:bg-green-50"
                                                                title="Edit Karyawan"
                                                            >
                                                                <Edit className="w-4 h-4" />
                                                            </Link>
                                                            <button
                                                                onClick={() => {
                                                                    if (
                                                                        confirm(
                                                                            `Apakah Anda yakin ingin menghapus karyawan ${employee.nama_lengkap}?`
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
                                                                className="p-1 text-red-600 transition-colors duration-200 rounded hover:text-red-900 hover:bg-red-50"
                                                                title="Hapus Karyawan"
                                                            >
                                                                <Trash2 className="w-4 h-4" />
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            )
                                        )}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    ) : (
                        <div className="bg-white border border-gray-200 rounded-lg shadow-sm">
                            <div className="flex flex-col items-center justify-center py-12">
                                <div className="flex items-center justify-center w-24 h-24 mx-auto mb-4 bg-gray-100 rounded-full">
                                    <Users className="w-8 h-8 text-gray-400" />
                                </div>
                                <h3 className="mb-2 text-lg font-medium text-gray-900">
                                    {hasActiveFilters()
                                        ? "Tidak ada data yang sesuai"
                                        : "Tidak ada data karyawan"}
                                </h3>
                                <p className="max-w-md mb-6 text-center text-gray-500">
                                    {hasActiveFilters()
                                        ? "Tidak ditemukan karyawan yang sesuai dengan filter yang Anda terapkan. Coba ubah atau hapus beberapa filter."
                                        : "Belum ada data karyawan yang ditambahkan. Mulai dengan menambahkan karyawan baru atau import data."}
                                </p>
                                {hasActiveFilters() ? (
                                    <button
                                        onClick={clearAllFilters}
                                        className="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-[#439454] bg-white border border-[#439454] rounded-lg hover:bg-[#439454] hover:text-white transition-colors duration-200"
                                    >
                                        <X className="w-4 h-4" />
                                        Hapus Semua Filter
                                    </button>
                                ) : (
                                    <Link
                                        href={route("employees.create")}
                                        className="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-[#439454] rounded-lg hover:bg-[#367a41] transition-colors duration-200"
                                    >
                                        <Plus className="w-4 h-4" />
                                        Tambah Karyawan Pertama
                                    </Link>
                                )}
                            </div>
                        </div>
                    )}
                </div>
            </div>

            {/* Employee Detail Modal - Using the Complete EmployeeDetailModal Component */}
            <EmployeeDetailModal
                employee={selectedEmployee}
                isOpen={showEmployeeModal}
                onClose={closeModal}
            />
        </DashboardLayout>
    );
}
