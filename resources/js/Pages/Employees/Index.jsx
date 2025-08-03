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
    ChevronDown,
    ChevronUp,
    ChevronLeft,
    ChevronRight,
    ChevronsLeft,
    ChevronsRight,
} from "lucide-react";

export default function Index({
    employees = { data: [] },
    pagination = {},
    filters = {},
    filterOptions = {},
    statistics = {}, // Add statistics from backend
    auth,
}) {
    // State management
    const [searchQuery, setSearchQuery] = useState(filters.search || "");
    const [statusFilter, setStatusFilter] = useState(
        filters.status_pegawai || "all"
    );
    const [unitFilter, setUnitFilter] = useState(
        filters.unit_organisasi || "all"
    );
    const [genderFilter, setGenderFilter] = useState(
        filters.jenis_kelamin || "all"
    );
    const [shoeTypeFilter, setShoeTypeFilter] = useState(
        filters.jenis_sepatu || "all"
    );
    const [shoeSizeFilter, setShoeSizeFilter] = useState(
        filters.ukuran_sepatu || "all"
    );
    const [perPage, setPerPage] = useState(pagination.per_page || 20);
    const [showFilters, setShowFilters] = useState(false);
    const [showEmployeeModal, setShowEmployeeModal] = useState(false);
    const [selectedEmployee, setSelectedEmployee] = useState(null);
    const [loading, setLoading] = useState(false);
    const [isNavigating, setIsNavigating] = useState(false);

    // Debounced search
    const [searchTimeout, setSearchTimeout] = useState(null);

    // Apply filters with backend pagination
    const applyFilters = (page = 1, newPerPage = perPage) => {
        setLoading(true);
        setIsNavigating(true);

        const params = {
            page,
            per_page: newPerPage,
        };

        // Add search query
        if (searchQuery.trim()) {
            params.search = searchQuery.trim();
        }

        // Add filters
        if (statusFilter !== "all") params.status_pegawai = statusFilter;
        if (unitFilter !== "all") params.unit_organisasi = unitFilter;
        if (genderFilter !== "all") params.jenis_kelamin = genderFilter;
        if (shoeTypeFilter !== "all") params.jenis_sepatu = shoeTypeFilter;
        if (shoeSizeFilter !== "all") params.ukuran_sepatu = shoeSizeFilter;

        router.visit(route("employees.index"), {
            data: params,
            preserveState: true,
            preserveScroll: true,
            onFinish: () => {
                setLoading(false);
                setTimeout(() => setIsNavigating(false), 300);
            },
        });
    };

    // Handle search with debounce
    const handleSearchChange = (value) => {
        setSearchQuery(value);

        if (searchTimeout) {
            clearTimeout(searchTimeout);
        }

        const timeout = setTimeout(() => {
            applyFilters(1); // Reset to page 1 on search
        }, 500);

        setSearchTimeout(timeout);
    };

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

        applyFilters(1); // Reset to page 1 on filter change
    };

    // Handle per page change
    const handlePerPageChange = (newPerPage) => {
        setPerPage(newPerPage);
        applyFilters(1, newPerPage); // Reset to page 1 with new per page
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

        router.visit(route("employees.index"), {
            preserveState: true,
            preserveScroll: true,
        });
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

    // Navigate to specific page
    const goToPage = (page) => {
        if (
            page !== pagination.current_page &&
            page >= 1 &&
            page <= pagination.last_page
        ) {
            applyFilters(page);
        }
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

    // Generate page numbers for pagination
    const generatePageNumbers = () => {
        const pages = [];
        const current = pagination.current_page;
        const last = pagination.last_page;

        if (last <= 7) {
            // Show all pages if total pages <= 7
            for (let i = 1; i <= last; i++) {
                pages.push(i);
            }
        } else {
            // Show smart pagination
            if (current <= 4) {
                // Show first 5 pages + ... + last page
                for (let i = 1; i <= 5; i++) {
                    pages.push(i);
                }
                if (last > 6) {
                    pages.push("...");
                    pages.push(last);
                }
            } else if (current >= last - 3) {
                // Show first page + ... + last 5 pages
                pages.push(1);
                if (last > 6) {
                    pages.push("...");
                }
                for (let i = last - 4; i <= last; i++) {
                    pages.push(i);
                }
            } else {
                // Show first + ... + current-1,current,current+1 + ... + last
                pages.push(1);
                pages.push("...");
                for (let i = current - 1; i <= current + 1; i++) {
                    pages.push(i);
                }
                pages.push("...");
                pages.push(last);
            }
        }

        return pages;
    };

    // Statistics from backend - includes global or filtered results
    const stats = useMemo(() => {
        return {
            total: statistics.total || 0,
            pegawaiTetap: statistics.pegawaiTetap || 0,
            tad: statistics.tad || 0,
            uniqueUnits: statistics.uniqueUnits || 0,
        };
    }, [statistics]);

    return (
        <DashboardLayout title="Management Karyawan">
            <Head title="Management Karyawan - GAPURA ANGKASA SDM">
                <style>{`
                    /* Custom dropdown styling */
                    select option {
                        background-color: white;
                        color: #374151;
                        padding: 8px 12px;
                        transition: all 0.2s ease-in-out;
                    }
                    
                    select option:hover {
                        background-color: #439454 !important;
                        color: white !important;
                    }
                    
                    select option:checked {
                        background-color: #439454 !important;
                        color: white !important;
                    }
                    
                    /* Enhanced dropdown animation */
                    select:focus {
                        animation: dropdownPulse 0.3s ease-in-out;
                    }
                    
                    @keyframes dropdownPulse {
                        0% { transform: scale(1); }
                        50% { transform: scale(1.02); }
                        100% { transform: scale(1.02); }
                    }
                    
                    /* Custom scrollbar for dropdown */
                    select::-webkit-scrollbar {
                        width: 6px;
                    }
                    
                    select::-webkit-scrollbar-track {
                        background: #f1f5f9;
                        border-radius: 10px;
                    }
                    
                    select::-webkit-scrollbar-thumb {
                        background: #439454;
                        border-radius: 10px;
                    }
                    
                    select::-webkit-scrollbar-thumb:hover {
                        background: #367a41;
                    }

                    /* Pagination animations */
                    @keyframes fadeIn {
                        from { opacity: 0; transform: translateY(10px); }
                        to { opacity: 1; transform: translateY(0); }
                    }
                    
                    @keyframes slideIn {
                        from { opacity: 0; transform: translateX(-20px); }
                        to { opacity: 1; transform: translateX(0); }
                    }
                    
                    @keyframes pageTransition {
                        0% { opacity: 1; transform: scale(1); }
                        50% { opacity: 0.7; transform: scale(0.98); }
                        100% { opacity: 1; transform: scale(1); }
                    }
                    
                    .animate-fadeIn {
                        animation: fadeIn 0.5s ease-out;
                    }
                    
                    .animate-slideIn {
                        animation: slideIn 0.3s ease-out;
                    }
                    
                    .animate-pageTransition {
                        animation: pageTransition 0.3s ease-in-out;
                    }
                    
                    /* Loading spinner */
                    @keyframes spin {
                        to { transform: rotate(360deg); }
                    }
                    
                    .animate-spin {
                        animation: spin 1s linear infinite;
                    }
                `}</style>
            </Head>

            <div className="min-h-screen bg-gradient-to-br from-gray-50 via-white to-gray-100">
                {/* Header Section */}
                <div className="sticky top-0 z-40 border-b border-gray-200 shadow-lg bg-white/95 backdrop-blur-sm">
                    <div className="px-6 py-8">
                        <div className="flex flex-col md:flex-row md:items-center md:justify-between">
                            <div className="space-y-2">
                                <h1 className="text-3xl font-bold tracking-tight text-gray-900">
                                    Management Karyawan
                                </h1>
                                <p className="font-medium text-gray-600">
                                    Kelola data karyawan PT Gapura Angkasa -
                                    Bandar Udara Ngurah Rai
                                </p>
                            </div>
                            <div className="flex gap-3 mt-6 md:mt-0">
                                <Link
                                    href={route("employees.import")}
                                    className="group inline-flex items-center gap-2 px-5 py-3 text-sm font-semibold text-gray-700 bg-white border-2 border-gray-300 rounded-xl hover:bg-gray-50 hover:border-[#439454] hover:text-[#439454] transition-all duration-300 shadow-sm hover:shadow-md transform hover:-translate-y-0.5"
                                >
                                    <FileUp className="w-4 h-4 transition-transform duration-300 group-hover:scale-110" />
                                    Import Data
                                </Link>
                                <Link
                                    href={route("employees.export")}
                                    className="group inline-flex items-center gap-2 px-5 py-3 text-sm font-semibold text-gray-700 bg-white border-2 border-gray-300 rounded-xl hover:bg-gray-50 hover:border-[#439454] hover:text-[#439454] transition-all duration-300 shadow-sm hover:shadow-md transform hover:-translate-y-0.5"
                                >
                                    <FileDown className="w-4 h-4 transition-transform duration-300 group-hover:scale-110" />
                                    Export Data
                                </Link>
                                <Link
                                    href={route("employees.create")}
                                    className="group inline-flex items-center gap-2 px-6 py-3 text-sm font-semibold text-white bg-gradient-to-r from-[#439454] to-[#367a41] rounded-xl hover:from-[#367a41] hover:to-[#2d6435] transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
                                >
                                    <Plus className="w-4 h-4 transition-transform duration-300 group-hover:rotate-90" />
                                    Tambah Karyawan
                                </Link>
                            </div>
                        </div>

                        {/* Enhanced Statistics Cards */}
                        <div className="grid grid-cols-1 gap-6 mt-8 sm:grid-cols-2 lg:grid-cols-4">
                            <div className="relative p-6 overflow-hidden transition-all duration-300 border-2 border-blue-200 group bg-gradient-to-br from-blue-50 to-blue-100 rounded-2xl hover:border-blue-300 hover:shadow-lg hover:-translate-y-1">
                                <div className="absolute inset-0 transition-opacity duration-300 opacity-0 bg-gradient-to-br from-blue-400/10 to-blue-600/10 group-hover:opacity-100"></div>
                                <div className="relative flex items-center">
                                    <div className="p-3 transition-transform duration-300 shadow-lg bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl group-hover:scale-110">
                                        <Users className="w-6 h-6 text-white" />
                                    </div>
                                    <div className="ml-4">
                                        <p className="text-sm font-semibold text-blue-800">
                                            Total Karyawan
                                        </p>
                                        <p className="text-3xl font-bold text-blue-700 transition-transform duration-300 group-hover:scale-105">
                                            {stats.total}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div className="relative p-6 overflow-hidden transition-all duration-300 border-2 border-green-200 group bg-gradient-to-br from-green-50 to-green-100 rounded-2xl hover:border-green-300 hover:shadow-lg hover:-translate-y-1">
                                <div className="absolute inset-0 transition-opacity duration-300 opacity-0 bg-gradient-to-br from-green-400/10 to-green-600/10 group-hover:opacity-100"></div>
                                <div className="relative flex items-center">
                                    <div className="p-3 transition-transform duration-300 shadow-lg bg-gradient-to-br from-green-500 to-green-600 rounded-xl group-hover:scale-110">
                                        <UserCheck className="w-6 h-6 text-white" />
                                    </div>
                                    <div className="ml-4">
                                        <p className="text-sm font-semibold text-green-800">
                                            Pegawai Tetap
                                        </p>
                                        <p className="text-3xl font-bold text-green-700 transition-transform duration-300 group-hover:scale-105">
                                            {stats.pegawaiTetap}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div className="relative p-6 overflow-hidden transition-all duration-300 border-2 border-yellow-200 group bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-2xl hover:border-yellow-300 hover:shadow-lg hover:-translate-y-1">
                                <div className="absolute inset-0 transition-opacity duration-300 opacity-0 bg-gradient-to-br from-yellow-400/10 to-yellow-600/10 group-hover:opacity-100"></div>
                                <div className="relative flex items-center">
                                    <div className="p-3 transition-transform duration-300 shadow-lg bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-xl group-hover:scale-110">
                                        <Calendar className="w-6 h-6 text-white" />
                                    </div>
                                    <div className="ml-4">
                                        <p className="text-sm font-semibold text-yellow-800">
                                            TAD
                                        </p>
                                        <p className="text-3xl font-bold text-yellow-700 transition-transform duration-300 group-hover:scale-105">
                                            {stats.tad}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div className="relative p-6 overflow-hidden transition-all duration-300 border-2 border-purple-200 group bg-gradient-to-br from-purple-50 to-purple-100 rounded-2xl hover:border-purple-300 hover:shadow-lg hover:-translate-y-1">
                                <div className="absolute inset-0 transition-opacity duration-300 opacity-0 bg-gradient-to-br from-purple-400/10 to-purple-600/10 group-hover:opacity-100"></div>
                                <div className="relative flex items-center">
                                    <div className="p-3 transition-transform duration-300 shadow-lg bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl group-hover:scale-110">
                                        <Building2 className="w-6 h-6 text-white" />
                                    </div>
                                    <div className="ml-4">
                                        <p className="text-sm font-semibold text-purple-800">
                                            Unit Organisasi
                                        </p>
                                        <p className="text-3xl font-bold text-purple-700 transition-transform duration-300 group-hover:scale-105">
                                            {stats.uniqueUnits}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Enhanced Search and Filter Section */}
                <div className="px-6 py-8 border-b border-gray-200 bg-white/80 backdrop-blur-sm">
                    {/* Enhanced Search Bar */}
                    <div className="flex gap-4 mb-6">
                        <div className="relative flex-1 group">
                            <div className="absolute inset-0 bg-gradient-to-r from-[#439454]/10 to-[#367a41]/10 rounded-xl opacity-0 group-hover:opacity-100 transition-all duration-300 transform scale-105"></div>
                            <div className="relative">
                                <Search className="absolute w-5 h-5 text-gray-400 transform -translate-y-1/2 left-4 top-1/2 group-hover:text-[#439454] group-focus-within:text-[#439454] transition-all duration-300 group-hover:scale-110" />
                                <input
                                    type="text"
                                    placeholder="Cari berdasarkan NIP, nama, jabatan, unit organisasi, instansi pendidikan..."
                                    value={searchQuery}
                                    onChange={(e) =>
                                        handleSearchChange(e.target.value)
                                    }
                                    className="w-full pl-12 pr-12 py-4 border-2 border-gray-300 rounded-xl focus:ring-4 focus:ring-[#439454]/20 focus:border-[#439454] hover:border-[#439454]/60 hover:shadow-lg transition-all duration-300 bg-white/90 placeholder-gray-500 text-gray-900 font-medium transform hover:scale-[1.02] focus:scale-[1.02]"
                                />

                                {/* Clear Search Button */}
                                {searchQuery && (
                                    <button
                                        onClick={() => handleSearchChange("")}
                                        className="absolute p-1 text-gray-400 transition-all duration-300 transform -translate-y-1/2 rounded-full right-12 top-1/2 hover:text-red-500 hover:scale-110 hover:bg-red-50"
                                        title="Hapus pencarian"
                                    >
                                        <X className="w-4 h-4" />
                                    </button>
                                )}

                                {loading && (
                                    <div className="absolute transform -translate-y-1/2 right-4 top-1/2">
                                        <div className="w-5 h-5 border-2 border-[#439454] border-t-transparent rounded-full animate-spin"></div>
                                    </div>
                                )}
                            </div>
                        </div>

                        <button
                            onClick={() => setShowFilters(!showFilters)}
                            className={`group inline-flex items-center gap-3 px-6 py-4 text-sm font-semibold border-2 rounded-xl transition-all duration-300 transform hover:-translate-y-0.5 ${
                                showFilters || hasActiveFilters()
                                    ? "text-white bg-gradient-to-r from-[#439454] to-[#367a41] border-[#439454] shadow-lg"
                                    : "text-gray-700 bg-white border-gray-300 hover:bg-[#439454] hover:text-white hover:border-[#439454] shadow-sm hover:shadow-md"
                            }`}
                        >
                            <Filter className="w-4 h-4 transition-transform duration-300 group-hover:scale-110" />
                            Filter
                            {showFilters ? (
                                <ChevronUp className="w-4 h-4 transition-transform duration-300" />
                            ) : (
                                <ChevronDown className="w-4 h-4 transition-transform duration-300" />
                            )}
                        </button>

                        {/* Clear All Button - Always visible */}
                        <button
                            onClick={clearAllFilters}
                            className={`group inline-flex items-center gap-3 px-6 py-4 text-sm font-semibold border-2 rounded-xl transition-all duration-300 shadow-sm hover:shadow-md transform hover:-translate-y-0.5 ${
                                hasActiveFilters()
                                    ? "text-red-600 bg-white border-red-300 hover:bg-red-50 hover:border-red-400 hover:text-red-700"
                                    : "text-gray-600 bg-white border-gray-300 hover:bg-gray-50 hover:border-gray-400 hover:text-gray-700"
                            }`}
                            title="Hapus semua filter dan pencarian"
                        >
                            <X className="w-4 h-4 transition-transform duration-300 group-hover:rotate-90" />
                            Clear All
                        </button>
                    </div>

                    {/* Enhanced Advanced Filters with Smooth Animation */}
                    <div
                        className={`overflow-hidden transition-all duration-500 ease-in-out ${
                            showFilters
                                ? "max-h-96 opacity-100"
                                : "max-h-0 opacity-0"
                        }`}
                    >
                        <div
                            className={`transform transition-all duration-500 ease-in-out ${
                                showFilters
                                    ? "translate-y-0 scale-100"
                                    : "translate-y-4 scale-95"
                            }`}
                        >
                            <div className="p-6 border-2 border-gray-200 shadow-inner bg-gradient-to-br from-gray-50 to-white rounded-2xl">
                                <div className="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-5">
                                    {/* Enhanced Filter Dropdowns */}
                                    <div className="relative group">
                                        <label className="block mb-3 text-sm font-semibold text-gray-700 group-hover:text-[#439454] transition-colors duration-300">
                                            Status Pegawai
                                        </label>
                                        <div className="relative">
                                            <div className="absolute inset-0 bg-gradient-to-r from-[#439454]/5 to-[#367a41]/5 rounded-xl opacity-0 group-hover:opacity-100 transition-all duration-300"></div>
                                            <select
                                                value={statusFilter}
                                                onChange={(e) =>
                                                    handleFilterChange(
                                                        "status",
                                                        e.target.value
                                                    )
                                                }
                                                className="relative w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:ring-4 focus:ring-[#439454]/20 focus:border-[#439454] hover:border-[#439454]/60 hover:shadow-md transition-all duration-300 bg-white font-medium appearance-none cursor-pointer transform hover:scale-[1.02] focus:scale-[1.02]"
                                                style={{
                                                    backgroundImage: "none",
                                                }}
                                            >
                                                <option value="all">
                                                    Semua Status
                                                </option>
                                                <option value="PEGAWAI TETAP">
                                                    Pegawai Tetap
                                                </option>
                                                <option value="TAD">TAD</option>
                                            </select>
                                            <ChevronDown className="absolute right-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400 group-hover:text-[#439454] transition-all duration-300 pointer-events-none group-hover:scale-110" />
                                        </div>
                                    </div>

                                    <div className="relative group">
                                        <label className="block mb-3 text-sm font-semibold text-gray-700 group-hover:text-[#439454] transition-colors duration-300">
                                            Unit Organisasi
                                        </label>
                                        <div className="relative">
                                            <div className="absolute inset-0 bg-gradient-to-r from-[#439454]/5 to-[#367a41]/5 rounded-xl opacity-0 group-hover:opacity-100 transition-all duration-300"></div>
                                            <select
                                                value={unitFilter}
                                                onChange={(e) =>
                                                    handleFilterChange(
                                                        "unit",
                                                        e.target.value
                                                    )
                                                }
                                                className="relative w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:ring-4 focus:ring-[#439454]/20 focus:border-[#439454] hover:border-[#439454]/60 hover:shadow-md transition-all duration-300 bg-white font-medium appearance-none cursor-pointer transform hover:scale-[1.02] focus:scale-[1.02]"
                                                style={{
                                                    backgroundImage: "none",
                                                }}
                                            >
                                                <option value="all">
                                                    Semua Unit
                                                </option>
                                                {(
                                                    filterOptions.units || []
                                                ).map((unit) => (
                                                    <option
                                                        key={unit}
                                                        value={unit}
                                                    >
                                                        {unit}
                                                    </option>
                                                ))}
                                            </select>
                                            <ChevronDown className="absolute right-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400 group-hover:text-[#439454] transition-all duration-300 pointer-events-none group-hover:scale-110" />
                                        </div>
                                    </div>

                                    <div className="relative group">
                                        <label className="block mb-3 text-sm font-semibold text-gray-700 group-hover:text-[#439454] transition-colors duration-300">
                                            Jenis Kelamin
                                        </label>
                                        <div className="relative">
                                            <div className="absolute inset-0 bg-gradient-to-r from-[#439454]/5 to-[#367a41]/5 rounded-xl opacity-0 group-hover:opacity-100 transition-all duration-300"></div>
                                            <select
                                                value={genderFilter}
                                                onChange={(e) =>
                                                    handleFilterChange(
                                                        "gender",
                                                        e.target.value
                                                    )
                                                }
                                                className="relative w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:ring-4 focus:ring-[#439454]/20 focus:border-[#439454] hover:border-[#439454]/60 hover:shadow-md transition-all duration-300 bg-white font-medium appearance-none cursor-pointer transform hover:scale-[1.02] focus:scale-[1.02]"
                                                style={{
                                                    backgroundImage: "none",
                                                }}
                                            >
                                                <option value="all">
                                                    Semua
                                                </option>
                                                <option value="Laki-laki">
                                                    Laki-laki
                                                </option>
                                                <option value="Perempuan">
                                                    Perempuan
                                                </option>
                                            </select>
                                            <ChevronDown className="absolute right-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400 group-hover:text-[#439454] transition-all duration-300 pointer-events-none group-hover:scale-110" />
                                        </div>
                                    </div>

                                    <div className="relative group">
                                        <label className="block mb-3 text-sm font-semibold text-gray-700 group-hover:text-[#439454] transition-colors duration-300">
                                            Jenis Sepatu
                                        </label>
                                        <div className="relative">
                                            <div className="absolute inset-0 bg-gradient-to-r from-[#439454]/5 to-[#367a41]/5 rounded-xl opacity-0 group-hover:opacity-100 transition-all duration-300"></div>
                                            <select
                                                value={shoeTypeFilter}
                                                onChange={(e) =>
                                                    handleFilterChange(
                                                        "shoeType",
                                                        e.target.value
                                                    )
                                                }
                                                className="relative w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:ring-4 focus:ring-[#439454]/20 focus:border-[#439454] hover:border-[#439454]/60 hover:shadow-md transition-all duration-300 bg-white font-medium appearance-none cursor-pointer transform hover:scale-[1.02] focus:scale-[1.02]"
                                                style={{
                                                    backgroundImage: "none",
                                                }}
                                            >
                                                <option value="all">
                                                    Semua Jenis
                                                </option>
                                                {(
                                                    filterOptions.shoe_types ||
                                                    []
                                                ).map((type) => (
                                                    <option
                                                        key={type}
                                                        value={type}
                                                    >
                                                        {type}
                                                    </option>
                                                ))}
                                            </select>
                                            <ChevronDown className="absolute right-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400 group-hover:text-[#439454] transition-all duration-300 pointer-events-none group-hover:scale-110" />
                                        </div>
                                    </div>

                                    <div className="relative group">
                                        <label className="block mb-3 text-sm font-semibold text-gray-700 group-hover:text-[#439454] transition-colors duration-300">
                                            Ukuran Sepatu
                                        </label>
                                        <div className="relative">
                                            <div className="absolute inset-0 bg-gradient-to-r from-[#439454]/5 to-[#367a41]/5 rounded-xl opacity-0 group-hover:opacity-100 transition-all duration-300"></div>
                                            <select
                                                value={shoeSizeFilter}
                                                onChange={(e) =>
                                                    handleFilterChange(
                                                        "shoeSize",
                                                        e.target.value
                                                    )
                                                }
                                                className="relative w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:ring-4 focus:ring-[#439454]/20 focus:border-[#439454] hover:border-[#439454]/60 hover:shadow-md transition-all duration-300 bg-white font-medium appearance-none cursor-pointer transform hover:scale-[1.02] focus:scale-[1.02]"
                                                style={{
                                                    backgroundImage: "none",
                                                }}
                                            >
                                                <option value="all">
                                                    Semua Ukuran
                                                </option>
                                                {(
                                                    filterOptions.shoe_sizes ||
                                                    []
                                                ).map((size) => (
                                                    <option
                                                        key={size}
                                                        value={size}
                                                    >
                                                        {size}
                                                    </option>
                                                ))}
                                            </select>
                                            <ChevronDown className="absolute right-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400 group-hover:text-[#439454] transition-all duration-300 pointer-events-none group-hover:scale-110" />
                                        </div>
                                    </div>
                                </div>

                                {/* Enhanced Clear Filters Button */}
                                {hasActiveFilters() && (
                                    <div className="mt-6 text-center">
                                        <button
                                            onClick={clearAllFilters}
                                            className="inline-flex items-center gap-2 px-6 py-3 text-sm font-semibold text-gray-600 transition-all duration-300 transform group hover:text-red-600 hover:scale-105"
                                        >
                                            <X className="w-4 h-4 transition-transform duration-300 group-hover:rotate-90" />
                                            Reset Semua Filter
                                        </button>
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>

                    {/* Enhanced Active Filters Display */}
                    {hasActiveFilters() && (
                        <div className="mt-6 animate-fadeIn">
                            <div className="flex flex-wrap items-center gap-3">
                                <span className="text-sm font-semibold text-gray-700">
                                    Filter aktif:
                                </span>
                                {statusFilter !== "all" && (
                                    <span className="inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold text-green-800 transition-all duration-300 transform bg-green-100 border border-green-200 rounded-full group hover:bg-green-200 hover:scale-105">
                                        Status: {statusFilter}
                                        <button
                                            onClick={() =>
                                                removeFilter("status")
                                            }
                                            className="text-green-600 transition-colors duration-300 hover:text-red-600"
                                        >
                                            <X className="w-3 h-3" />
                                        </button>
                                    </span>
                                )}
                                {unitFilter !== "all" && (
                                    <span className="inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold text-purple-800 transition-all duration-300 transform bg-purple-100 border border-purple-200 rounded-full group hover:bg-purple-200 hover:scale-105">
                                        Unit: {unitFilter}
                                        <button
                                            onClick={() => removeFilter("unit")}
                                            className="text-purple-600 transition-colors duration-300 hover:text-red-600"
                                        >
                                            <X className="w-3 h-3" />
                                        </button>
                                    </span>
                                )}
                                {genderFilter !== "all" && (
                                    <span className="inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold text-orange-800 transition-all duration-300 transform bg-orange-100 border border-orange-200 rounded-full group hover:bg-orange-200 hover:scale-105">
                                        Gender: {genderFilter}
                                        <button
                                            onClick={() =>
                                                removeFilter("gender")
                                            }
                                            className="text-orange-600 transition-colors duration-300 hover:text-red-600"
                                        >
                                            <X className="w-3 h-3" />
                                        </button>
                                    </span>
                                )}
                                {shoeTypeFilter !== "all" && (
                                    <span className="inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold text-blue-800 transition-all duration-300 transform bg-blue-100 border border-blue-200 rounded-full group hover:bg-blue-200 hover:scale-105">
                                        Sepatu: {shoeTypeFilter}
                                        <button
                                            onClick={() =>
                                                removeFilter("shoeType")
                                            }
                                            className="text-blue-600 transition-colors duration-300 hover:text-red-600"
                                        >
                                            <X className="w-3 h-3" />
                                        </button>
                                    </span>
                                )}
                                {shoeSizeFilter !== "all" && (
                                    <span className="inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold text-indigo-800 transition-all duration-300 transform bg-indigo-100 border border-indigo-200 rounded-full group hover:bg-indigo-200 hover:scale-105">
                                        Ukuran: {shoeSizeFilter}
                                        <button
                                            onClick={() =>
                                                removeFilter("shoeSize")
                                            }
                                            className="text-indigo-600 transition-colors duration-300 hover:text-red-600"
                                        >
                                            <X className="w-3 h-3" />
                                        </button>
                                    </span>
                                )}
                            </div>

                            {/* Enhanced Results Count */}
                            <div className="flex items-center justify-between mt-4">
                                <div className="space-y-1">
                                    <div className="inline-block px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 rounded-lg">
                                        Menampilkan{" "}
                                        <span className="text-[#439454] font-bold mx-1">
                                            {pagination.from || 0}
                                        </span>{" "}
                                        -
                                        <span className="text-[#439454] font-bold mx-1">
                                            {pagination.to || 0}
                                        </span>{" "}
                                        dari{" "}
                                        <span className="text-[#439454] font-bold mx-1">
                                            {pagination.total || 0}
                                        </span>{" "}
                                        {hasActiveFilters()
                                            ? "hasil filter"
                                            : "karyawan"}
                                    </div>
                                    {hasActiveFilters() && (
                                        <div className="text-xs text-gray-500">
                                            {hasActiveFilters()
                                                ? `Filter aktif dari ${stats.total} total karyawan`
                                                : ""}
                                        </div>
                                    )}
                                </div>

                                {/* Per Page Selector */}
                                <div className="flex items-center gap-3">
                                    <span className="text-sm font-medium text-gray-600">
                                        Tampilkan:
                                    </span>
                                    <select
                                        value={perPage}
                                        onChange={(e) =>
                                            handlePerPageChange(
                                                parseInt(e.target.value)
                                            )
                                        }
                                        className="px-3 py-2 text-sm font-medium border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-[#439454]/20 focus:border-[#439454] hover:border-[#439454]/60 transition-all duration-300 bg-white cursor-pointer"
                                    >
                                        <option value={10}>10</option>
                                        <option value={20}>20</option>
                                        <option value={50}>50</option>
                                        <option value={100}>100</option>
                                    </select>
                                    <span className="text-sm font-medium text-gray-600">
                                        per halaman
                                    </span>
                                </div>
                            </div>
                        </div>
                    )}
                </div>

                {/* Enhanced Employee Table */}
                <div className="px-6 pb-8">
                    {employees.data && employees.data.length > 0 ? (
                        <div className="overflow-hidden bg-white border-2 border-gray-200 shadow-xl rounded-2xl">
                            <div
                                className={`overflow-x-auto ${
                                    isNavigating ? "animate-pageTransition" : ""
                                }`}
                            >
                                <table className="min-w-full divide-y divide-gray-200">
                                    <thead className="bg-gradient-to-r from-gray-50 to-gray-100">
                                        <tr>
                                            <th className="px-6 py-4 text-xs font-bold tracking-wider text-left text-gray-600 uppercase">
                                                No
                                            </th>
                                            <th className="px-6 py-4 text-xs font-bold tracking-wider text-left text-gray-600 uppercase">
                                                NIP
                                            </th>
                                            <th className="px-6 py-4 text-xs font-bold tracking-wider text-left text-gray-600 uppercase">
                                                Nama Lengkap
                                            </th>
                                            <th className="px-6 py-4 text-xs font-bold tracking-wider text-left text-gray-600 uppercase">
                                                Status Pegawai
                                            </th>
                                            <th className="px-6 py-4 text-xs font-bold tracking-wider text-left text-gray-600 uppercase">
                                                TMT Mulai Jabatan
                                            </th>
                                            <th className="px-6 py-4 text-xs font-bold tracking-wider text-center text-gray-600 uppercase">
                                                Aksi
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody className="bg-white divide-y divide-gray-200">
                                        {employees.data.map(
                                            (employee, index) => (
                                                <tr
                                                    key={
                                                        employee.id ||
                                                        employee.nip ||
                                                        index
                                                    }
                                                    className="group hover:bg-gradient-to-r hover:from-[#439454]/5 hover:to-[#367a41]/5 transition-all duration-300"
                                                >
                                                    <td className="px-6 py-5 text-sm font-bold text-gray-900 whitespace-nowrap group-hover:text-[#439454] transition-colors duration-300">
                                                        {(pagination.current_page -
                                                            1) *
                                                            pagination.per_page +
                                                            index +
                                                            1}
                                                    </td>
                                                    <td className="px-6 py-5 whitespace-nowrap">
                                                        <div className="flex items-center">
                                                            <div className="flex-shrink-0 w-10 h-10">
                                                                <div className="w-10 h-10 bg-gradient-to-br from-[#439454] to-[#367a41] rounded-full flex items-center justify-center text-white text-sm font-bold shadow-lg group-hover:scale-110 transition-transform duration-300">
                                                                    {getInitials(
                                                                        employee.nama_lengkap
                                                                    )}
                                                                </div>
                                                            </div>
                                                            <div className="ml-4">
                                                                <div className="text-sm font-bold text-gray-900 group-hover:text-[#439454] transition-colors duration-300">
                                                                    {employee.nip ||
                                                                        "-"}
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td className="px-6 py-5 whitespace-nowrap">
                                                        <div className="text-sm font-bold text-gray-900 group-hover:text-[#439454] transition-colors duration-300">
                                                            {employee.nama_lengkap ||
                                                                "-"}
                                                        </div>
                                                        <div className="text-sm font-medium text-gray-500">
                                                            {employee.nama_jabatan ||
                                                                employee.jabatan ||
                                                                "-"}
                                                        </div>
                                                    </td>
                                                    <td className="px-6 py-5 whitespace-nowrap">
                                                        <span
                                                            className={`inline-flex px-3 py-2 text-xs font-bold rounded-full shadow-sm transition-all duration-300 group-hover:scale-105 ${
                                                                employee.status_pegawai ===
                                                                "PEGAWAI TETAP"
                                                                    ? "bg-gradient-to-r from-green-100 to-green-200 text-green-800 border border-green-300"
                                                                    : "bg-gradient-to-r from-yellow-100 to-yellow-200 text-yellow-800 border border-yellow-300"
                                                            }`}
                                                        >
                                                            {employee.status_pegawai ||
                                                                "-"}
                                                        </span>
                                                    </td>
                                                    <td className="px-6 py-5 text-sm font-semibold text-gray-900 whitespace-nowrap group-hover:text-[#439454] transition-colors duration-300">
                                                        {formatDate(
                                                            employee.tmt_mulai_jabatan
                                                        )}
                                                    </td>
                                                    <td className="px-6 py-5 text-sm font-medium whitespace-nowrap">
                                                        <div className="flex items-center justify-center gap-2">
                                                            <button
                                                                onClick={() =>
                                                                    showEmployeeDetails(
                                                                        employee
                                                                    )
                                                                }
                                                                className="p-2 text-blue-600 transition-all duration-300 transform group/btn rounded-xl hover:text-white hover:bg-blue-600 hover:shadow-lg hover:scale-110"
                                                                title="Lihat Detail Lengkap"
                                                            >
                                                                <Eye className="w-4 h-4 transition-transform duration-300 group-hover/btn:scale-110" />
                                                            </button>
                                                            <Link
                                                                href={route(
                                                                    "employees.edit",
                                                                    employee.id
                                                                )}
                                                                className="group/btn p-2 text-[#439454] transition-all duration-300 rounded-xl hover:text-white hover:bg-[#439454] hover:shadow-lg transform hover:scale-110"
                                                                title="Edit Karyawan"
                                                            >
                                                                <Edit className="w-4 h-4 transition-transform duration-300 group-hover/btn:scale-110" />
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
                                                                className="p-2 text-red-600 transition-all duration-300 transform group/btn rounded-xl hover:text-white hover:bg-red-600 hover:shadow-lg hover:scale-110"
                                                                title="Hapus Karyawan"
                                                            >
                                                                <Trash2 className="w-4 h-4 transition-transform duration-300 group-hover/btn:scale-110" />
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            )
                                        )}
                                    </tbody>
                                </table>
                            </div>

                            {/* Enhanced Pagination Component */}
                            {pagination.has_pages && (
                                <div className="px-6 py-6 border-t border-gray-200 bg-gradient-to-r from-gray-50 to-white">
                                    <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                        {/* Pagination Info */}
                                        <div className="text-sm font-medium text-gray-600">
                                            Menampilkan{" "}
                                            <span className="font-bold text-[#439454]">
                                                {pagination.from}
                                            </span>{" "}
                                            sampai{" "}
                                            <span className="font-bold text-[#439454]">
                                                {pagination.to}
                                            </span>{" "}
                                            dari{" "}
                                            <span className="font-bold text-[#439454]">
                                                {pagination.total}
                                            </span>{" "}
                                            hasil
                                        </div>

                                        {/* Pagination Navigation */}
                                        <div className="flex items-center gap-2">
                                            {/* First Page */}
                                            <button
                                                onClick={() => goToPage(1)}
                                                disabled={
                                                    pagination.on_first_page ||
                                                    loading
                                                }
                                                className={`group p-2 rounded-lg transition-all duration-300 transform hover:scale-110 ${
                                                    pagination.on_first_page ||
                                                    loading
                                                        ? "text-gray-400 cursor-not-allowed"
                                                        : "text-gray-600 hover:text-[#439454] hover:bg-[#439454]/10"
                                                }`}
                                                title="Halaman Pertama"
                                            >
                                                <ChevronsLeft className="w-5 h-5 transition-transform duration-300 group-hover:scale-110" />
                                            </button>

                                            {/* Previous Page */}
                                            <button
                                                onClick={() =>
                                                    goToPage(
                                                        pagination.current_page -
                                                            1
                                                    )
                                                }
                                                disabled={
                                                    pagination.on_first_page ||
                                                    loading
                                                }
                                                className={`group p-2 rounded-lg transition-all duration-300 transform hover:scale-110 ${
                                                    pagination.on_first_page ||
                                                    loading
                                                        ? "text-gray-400 cursor-not-allowed"
                                                        : "text-gray-600 hover:text-[#439454] hover:bg-[#439454]/10"
                                                }`}
                                                title="Halaman Sebelumnya"
                                            >
                                                <ChevronLeft className="w-5 h-5 transition-transform duration-300 group-hover:scale-110" />
                                            </button>

                                            {/* Page Numbers */}
                                            <div className="flex gap-1">
                                                {generatePageNumbers().map(
                                                    (page, index) =>
                                                        page === "..." ? (
                                                            <span
                                                                key={`ellipsis-${index}`}
                                                                className="px-3 py-2 text-sm font-medium text-gray-400"
                                                            >
                                                                ...
                                                            </span>
                                                        ) : (
                                                            <button
                                                                key={page}
                                                                onClick={() =>
                                                                    goToPage(
                                                                        page
                                                                    )
                                                                }
                                                                disabled={
                                                                    loading
                                                                }
                                                                className={`group px-4 py-2 text-sm font-bold rounded-lg transition-all duration-300 transform hover:scale-110 ${
                                                                    page ===
                                                                    pagination.current_page
                                                                        ? "text-white bg-gradient-to-r from-[#439454] to-[#367a41] shadow-lg"
                                                                        : "text-gray-600 hover:text-[#439454] hover:bg-[#439454]/10"
                                                                }`}
                                                            >
                                                                {page}
                                                            </button>
                                                        )
                                                )}
                                            </div>

                                            {/* Next Page */}
                                            <button
                                                onClick={() =>
                                                    goToPage(
                                                        pagination.current_page +
                                                            1
                                                    )
                                                }
                                                disabled={
                                                    pagination.on_last_page ||
                                                    loading
                                                }
                                                className={`group p-2 rounded-lg transition-all duration-300 transform hover:scale-110 ${
                                                    pagination.on_last_page ||
                                                    loading
                                                        ? "text-gray-400 cursor-not-allowed"
                                                        : "text-gray-600 hover:text-[#439454] hover:bg-[#439454]/10"
                                                }`}
                                                title="Halaman Selanjutnya"
                                            >
                                                <ChevronRight className="w-5 h-5 transition-transform duration-300 group-hover:scale-110" />
                                            </button>

                                            {/* Last Page */}
                                            <button
                                                onClick={() =>
                                                    goToPage(
                                                        pagination.last_page
                                                    )
                                                }
                                                disabled={
                                                    pagination.on_last_page ||
                                                    loading
                                                }
                                                className={`group p-2 rounded-lg transition-all duration-300 transform hover:scale-110 ${
                                                    pagination.on_last_page ||
                                                    loading
                                                        ? "text-gray-400 cursor-not-allowed"
                                                        : "text-gray-600 hover:text-[#439454] hover:bg-[#439454]/10"
                                                }`}
                                                title="Halaman Terakhir"
                                            >
                                                <ChevronsRight className="w-5 h-5 transition-transform duration-300 group-hover:scale-110" />
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            )}
                        </div>
                    ) : (
                        <div className="bg-white border-2 border-gray-200 shadow-xl rounded-2xl">
                            <div className="flex flex-col items-center justify-center py-16">
                                <div className="flex items-center justify-center w-32 h-32 mx-auto mb-6 rounded-full bg-gradient-to-br from-gray-100 to-gray-200">
                                    <Users className="w-12 h-12 text-gray-400" />
                                </div>
                                <h3 className="mb-3 text-xl font-bold text-gray-900">
                                    {hasActiveFilters()
                                        ? "Tidak ada data yang sesuai"
                                        : "Tidak ada data karyawan"}
                                </h3>
                                <p className="max-w-md mb-8 font-medium text-center text-gray-500">
                                    {hasActiveFilters()
                                        ? "Tidak ditemukan karyawan yang sesuai dengan filter yang Anda terapkan. Coba ubah atau hapus beberapa filter."
                                        : "Belum ada data karyawan yang ditambahkan. Mulai dengan menambahkan karyawan baru atau import data."}
                                </p>
                                {hasActiveFilters() ? (
                                    <button
                                        onClick={clearAllFilters}
                                        className="group inline-flex items-center gap-3 px-6 py-3 text-sm font-semibold text-[#439454] bg-white border-2 border-[#439454] rounded-xl hover:bg-[#439454] hover:text-white transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
                                    >
                                        <X className="w-4 h-4 transition-transform duration-300 group-hover:rotate-90" />
                                        Hapus Semua Filter
                                    </button>
                                ) : (
                                    <Link
                                        href={route("employees.create")}
                                        className="group inline-flex items-center gap-3 px-6 py-3 text-sm font-semibold text-white bg-gradient-to-r from-[#439454] to-[#367a41] rounded-xl hover:from-[#367a41] hover:to-[#2d6435] transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
                                    >
                                        <Plus className="w-4 h-4 transition-transform duration-300 group-hover:rotate-90" />
                                        Tambah Karyawan Pertama
                                    </Link>
                                )}
                            </div>
                        </div>
                    )}
                </div>
            </div>

            {/* Employee Detail Modal */}
            <EmployeeDetailModal
                employee={selectedEmployee}
                isOpen={showEmployeeModal}
                onClose={closeModal}
            />
        </DashboardLayout>
    );
}
