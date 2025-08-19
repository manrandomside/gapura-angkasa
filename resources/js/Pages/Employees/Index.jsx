import React, { useState, useEffect, useMemo } from "react";
import { Head, Link, router } from "@inertiajs/react";
import DashboardLayout from "@/Layouts/DashboardLayout";
import EmployeeDetailModal from "@/Components/EmployeeDetailModal";
import axios from "axios";
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
    Star,
    Clock,
} from "lucide-react";

export default function Index({
    employees = { data: [] },
    pagination = {},
    filters = {},
    filterOptions = {},
    statistics = {},
    notifications = {},
    newEmployee = null,
    success = null,
    error = null,
    message = null,
    notification = null,
    alerts = [],
    title = "Management Karyawan",
    subtitle = "Kelola data karyawan PT Gapura Angkasa - Bandar Udara Ngurah Rai",
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
    // ENHANCED: Tambah filter untuk unit dan sub unit
    const [unitIdFilter, setUnitIdFilter] = useState(filters.unit_id || "all");
    const [subUnitIdFilter, setSubUnitIdFilter] = useState(
        filters.sub_unit_id || "all"
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
    // Filter kelompok jabatan
    const [kelompokJabatanFilter, setKelompokJabatanFilter] = useState(
        filters.kelompok_jabatan || "all"
    );
    const [perPage, setPerPage] = useState(pagination.per_page || 20);
    const [showFilters, setShowFilters] = useState(false);
    const [showEmployeeModal, setShowEmployeeModal] = useState(false);
    const [selectedEmployee, setSelectedEmployee] = useState(null);
    const [loading, setLoading] = useState(false);
    const [isNavigating, setIsNavigating] = useState(false);

    // ENHANCED: State untuk cascading dropdown unit dengan static data
    const [availableUnits, setAvailableUnits] = useState([]);
    const [availableSubUnits, setAvailableSubUnits] = useState([]);

    // Debounced search
    const [searchTimeout, setSearchTimeout] = useState(null);

    // Simplified state untuk tracking karyawan baru
    const [newEmployeeIds, setNewEmployeeIds] = useState(new Set());
    const [clickedEmployees, setClickedEmployees] = useState(new Set());
    const [autoHiddenEmployees, setAutoHiddenEmployees] = useState(new Set());

    // STATIC: Struktur organisasi lengkap sesuai dengan create employee
    const organizationStructure = {
        EGM: {
            units: ["EGM"],
            subUnits: {
                EGM: [],
            },
        },
        GM: {
            units: ["GM"],
            subUnits: {
                GM: [],
            },
        },
        Airside: {
            units: ["MO", "ME"],
            subUnits: {
                MO: [
                    "Flops",
                    "Depco",
                    "Ramp",
                    "Load Control",
                    "Load Master",
                    "ULD Control",
                    "Cargo Import",
                    "Cargo Export",
                ],
                ME: [
                    "GSE Operator P/B",
                    "GSE Operator A/C",
                    "GSE Maintenance",
                    "BTT Operator",
                    "Line Maintenance",
                ],
            },
        },
        Landside: {
            units: ["MF", "MS"],
            subUnits: {
                MF: [
                    "KLM",
                    "Qatar",
                    "Korean Air",
                    "Vietjet Air",
                    "Scoot",
                    "Thai Airways",
                    "China Airlines",
                    "China Southern",
                    "Indigo",
                    "Xiamen Air",
                    "Aero Dili",
                    "Jeju Air",
                    "Hongkong Airlines",
                    "Air Busan",
                    "Vietnam Airlines",
                    "Sichuan Airlines",
                    "Aeroflot",
                    "Charter Flight",
                ],
                MS: ["MPGA", "QG", "IP"],
            },
        },
        "Back Office": {
            units: ["MU", "MK"],
            subUnits: {
                MU: ["Human Resources & General Affair", "Fasilitas & Sarana"],
                MK: ["Accounting", "Budgeting", "Treassury", "Tax"],
            },
        },
        SSQC: {
            units: ["MQ"],
            subUnits: {
                MQ: ["Avsec", "Safety Quality Control"],
            },
        },
        Ancillary: {
            units: ["MB"],
            subUnits: {
                MB: ["GPL", "GLC", "Joumpa"],
            },
        },
    };

    // FIXED: Enhanced updateUnits dengan kombinasi static data dan API validation
    const updateUnitsWithAPI = async (unitOrganisasi) => {
        if (!unitOrganisasi || unitOrganisasi === "all") {
            setAvailableUnits([]);
            setAvailableSubUnits([]);
            return;
        }

        try {
            console.log(`Updating units for: ${unitOrganisasi}`); // Debug log

            // FIXED: Gunakan static data sebagai primary source
            const structure = organizationStructure[unitOrganisasi];
            if (structure) {
                setAvailableUnits(structure.units);
                setAvailableSubUnits([]);
            }

            // Optional: Validate dengan API jika tersedia
            try {
                const response = await axios.get("/api/units", {
                    params: { unit_organisasi: unitOrganisasi },
                });

                if (response.data && response.data.length > 0) {
                    // Cross-validate dengan static data
                    const apiUnits = response.data.map(
                        (unit) => unit.value || unit.name
                    );
                    const staticUnits = structure ? structure.units : [];

                    // Gunakan intersection antara static dan API data
                    const validatedUnits = staticUnits.filter(
                        (unit) =>
                            apiUnits.length === 0 || apiUnits.includes(unit)
                    );

                    if (validatedUnits.length > 0) {
                        setAvailableUnits(validatedUnits);
                        console.log(
                            `API validated units: ${validatedUnits.join(", ")}`
                        ); // Debug log
                    }
                }
            } catch (apiError) {
                console.log(
                    "API validation failed, using static data:",
                    apiError.message
                );
                // Tetap gunakan static data jika API gagal
            }
        } catch (error) {
            console.error("Error updating units:", error);
            setAvailableUnits([]);
            setAvailableSubUnits([]);
        }
    };

    // FIXED: Enhanced updateSubUnits dengan kombinasi static data dan API validation
    const updateSubUnitsWithAPI = async (unit) => {
        if (!unit || unit === "all" || !unitFilter || unitFilter === "all") {
            setAvailableSubUnits([]);
            return;
        }

        try {
            console.log(
                `Updating sub units for unit: ${unit} in ${unitFilter}`
            ); // Debug log

            // FIXED: Gunakan static data sebagai primary source
            const structure = organizationStructure[unitFilter];
            if (structure && structure.subUnits[unit]) {
                setAvailableSubUnits(structure.subUnits[unit]);
            }

            // Optional: Validate dengan API jika tersedia
            try {
                const response = await axios.get("/api/sub-units", {
                    params: { unit: unit },
                });

                if (response.data && response.data.length > 0) {
                    // Cross-validate dengan static data
                    const apiSubUnits = response.data.map(
                        (subUnit) => subUnit.value || subUnit.name
                    );
                    const staticSubUnits =
                        structure && structure.subUnits[unit]
                            ? structure.subUnits[unit]
                            : [];

                    // Gunakan intersection antara static dan API data
                    const validatedSubUnits = staticSubUnits.filter(
                        (subUnit) =>
                            apiSubUnits.length === 0 ||
                            apiSubUnits.includes(subUnit)
                    );

                    if (validatedSubUnits.length > 0) {
                        setAvailableSubUnits(validatedSubUnits);
                        console.log(
                            `API validated sub units: ${validatedSubUnits.join(
                                ", "
                            )}`
                        ); // Debug log
                    }
                }
            } catch (apiError) {
                console.log(
                    "SubUnit API validation failed, using static data:",
                    apiError.message
                );
                // Tetap gunakan static data jika API gagal
            }
        } catch (error) {
            console.error("Error updating sub units:", error);
            setAvailableSubUnits([]);
        }
    };

    // ENHANCED: Update units berdasarkan unit organisasi yang dipilih (legacy function untuk compatibility)
    const updateUnits = (unitOrganisasi) => {
        updateUnitsWithAPI(unitOrganisasi);
    };

    // ENHANCED: Update sub units berdasarkan unit yang dipilih (legacy function untuk compatibility)
    const updateSubUnits = (unit) => {
        updateSubUnitsWithAPI(unit);
    };

    // FIXED: Enhanced useEffect untuk initial load cascading dropdown
    useEffect(() => {
        console.log("Unit filter changed:", unitFilter); // Debug log

        if (unitFilter && unitFilter !== "all") {
            updateUnitsWithAPI(unitFilter);

            // Jika ada unitIdFilter juga, load sub units
            if (unitIdFilter && unitIdFilter !== "all") {
                setTimeout(() => {
                    updateSubUnitsWithAPI(unitIdFilter);
                }, 100);
            }
        } else {
            setAvailableUnits([]);
            setAvailableSubUnits([]);
        }
    }, [unitFilter]);

    useEffect(() => {
        console.log("Unit ID filter changed:", unitIdFilter); // Debug log

        if (
            unitIdFilter &&
            unitIdFilter !== "all" &&
            unitFilter &&
            unitFilter !== "all"
        ) {
            updateSubUnitsWithAPI(unitIdFilter);
        } else {
            setAvailableSubUnits([]);
        }
    }, [unitIdFilter]);

    // FIXED: Debug useEffect untuk monitoring search state
    useEffect(() => {
        console.log("Current filter state:", {
            search: searchQuery,
            status: statusFilter,
            unitOrganisasi: unitFilter,
            unit: unitIdFilter,
            subUnit: subUnitIdFilter,
            availableUnits,
            availableSubUnits,
        });
    }, [
        searchQuery,
        statusFilter,
        unitFilter,
        unitIdFilter,
        subUnitIdFilter,
        availableUnits,
        availableSubUnits,
    ]);

    // MODIFIED: Effect untuk handle karyawan baru dengan auto-hide 24 jam
    useEffect(() => {
        if (newEmployee) {
            // Tambahkan ID karyawan baru ke set
            setNewEmployeeIds((prev) => new Set([...prev, newEmployee.id]));

            // Auto-scroll ke karyawan baru setelah data dimuat
            setTimeout(() => {
                const newEmployeeRow = document.querySelector(
                    `[data-employee-id="${newEmployee.id}"]`
                );
                if (newEmployeeRow) {
                    newEmployeeRow.scrollIntoView({
                        behavior: "smooth",
                        block: "center",
                    });
                }
            }, 500);

            // MODIFIED: Auto-hide "Baru Ditambahkan" label setelah 24 jam
            setTimeout(() => {
                setAutoHiddenEmployees((prev) => {
                    const updated = new Set(prev);
                    updated.add(newEmployee.id);
                    return updated;
                });
            }, 86400000); // 24 jam = 86400000ms
        }
    }, [newEmployee]);

    // Enhanced notification handling untuk backend notifications
    useEffect(() => {
        if (notification) {
            // Handle notification dari backend (success, error, dll)
            // Bisa ditambahkan toast notification sederhana di sini
        }

        if (success) {
            // Handle success message
        }

        if (error) {
            // Handle error message
        }
    }, [notification, success, error, message]);

    // MODIFIED: Auto-hide untuk karyawan yang dibuat dalam rentang waktu 24 jam
    useEffect(() => {
        const newEmployeeIdsArray =
            employees.data
                ?.filter((employee) => {
                    const createdAt = new Date(employee.created_at);
                    const now = new Date();
                    const diffInHours = (now - createdAt) / (1000 * 60 * 60);
                    return diffInHours <= 24 && diffInHours > 0;
                })
                .map((employee) => employee.id) || [];

        if (newEmployeeIdsArray.length > 0) {
            newEmployeeIdsArray.forEach((employeeId) => {
                // Set timer untuk auto-hide setelah 24 jam dari created_at
                const employee = employees.data.find(
                    (emp) => emp.id === employeeId
                );
                if (employee) {
                    const createdAt = new Date(employee.created_at);
                    const now = new Date();
                    const remainingTime = 86400000 - (now - createdAt); // 24 jam - waktu yang sudah lewat

                    if (remainingTime > 0) {
                        const timer = setTimeout(() => {
                            setAutoHiddenEmployees((prev) => {
                                const updated = new Set(prev);
                                updated.add(employeeId);
                                return updated;
                            });
                        }, remainingTime);

                        return () => clearTimeout(timer);
                    }
                }
            });
        }
    }, [employees.data]);

    // Check if employee should show "Baru Ditambahkan" label
    const isNewEmployee = (employee) => {
        // Don't show if user has clicked on this employee's profile
        if (clickedEmployees.has(employee.id)) {
            return false;
        }

        // Don't show if auto-hidden after 24 hours
        if (autoHiddenEmployees.has(employee.id)) {
            return false;
        }

        // Show if this is the newly created employee from session
        if (newEmployee && employee.id === newEmployee.id) {
            return true;
        }

        // Show if this employee is in the newEmployeeIds set
        if (newEmployeeIds.has(employee.id)) {
            return true;
        }

        // Enhanced: Check dari notifications.newToday
        if (notifications?.newToday && Array.isArray(notifications.newToday)) {
            return notifications.newToday.some(
                (newEmp) => newEmp.id === employee.id
            );
        }

        // Show if created within last 24 hours
        const createdAt = new Date(employee.created_at);
        const now = new Date();
        const diffInHours = (now - createdAt) / (1000 * 60 * 60);
        return diffInHours <= 24 && diffInHours > 0;
    };

    // Handle click on employee profile elements
    const handleEmployeeProfileClick = (
        employeeId,
        event,
        action = "hide-label"
    ) => {
        // Prevent event bubbling to table row
        event.stopPropagation();

        if (action === "hide-label") {
            // Add to clicked employees to hide "Baru Ditambahkan" label
            setClickedEmployees((prev) => {
                const updated = new Set(prev);
                updated.add(employeeId);
                return updated;
            });

            // Also remove from newEmployeeIds if present
            setNewEmployeeIds((prev) => {
                const updated = new Set(prev);
                updated.delete(employeeId);
                return updated;
            });
        }
    };

    // Enhanced function untuk handle klik pada karyawan
    const handleEmployeeClick = (employeeId, action = "view") => {
        // Hapus dari daftar karyawan baru
        if (newEmployeeIds.has(employeeId)) {
            setNewEmployeeIds((prev) => {
                const updated = new Set(prev);
                updated.delete(employeeId);
                return updated;
            });
        }

        // Add to clicked employees
        setClickedEmployees((prev) => {
            const updated = new Set(prev);
            updated.add(employeeId);
            return updated;
        });

        // Jalankan aksi sesuai parameter
        if (action === "view") {
            const employee = employees.data.find(
                (emp) => emp.id === employeeId
            );
            if (employee) {
                setSelectedEmployee(employee);
                setShowEmployeeModal(true);
            }
        } else if (action === "edit") {
            router.visit(route("employees.edit", employeeId));
        }
    };

    // MODIFIED: Simplified komponen untuk label "baru ditambahkan" - diperkecil dan clickable
    const SimplifiedNewEmployeeLabel = ({ employee }) => {
        if (!isNewEmployee(employee)) return null;

        return (
            <span
                className="absolute -top-1 left-1/2 transform -translate-x-1/2 px-1.5 py-0.5 text-2xs font-medium text-white bg-[#439454] rounded-full cursor-pointer hover:bg-[#367a41] transition-colors duration-200 whitespace-nowrap z-10 shadow-sm"
                style={{ fontSize: "10px" }}
                onClick={(e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    handleEmployeeProfileClick(employee.id, e);
                }}
                title="Klik untuk menyembunyikan"
            >
                baru ditambahkan
            </span>
        );
    };

    // FIXED: Enhanced applyFilters dengan parameter yang benar
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

        // FIXED: Add filters dengan nama parameter yang konsisten dengan backend
        if (statusFilter !== "all") params.status_pegawai = statusFilter;
        if (unitFilter !== "all") params.unit_organisasi = unitFilter;

        // FIXED: Gunakan nama unit/sub unit bukan ID untuk konsistensi dengan static data
        if (unitIdFilter !== "all") params.unit_id = unitIdFilter;
        if (subUnitIdFilter !== "all") params.sub_unit_id = subUnitIdFilter;

        if (genderFilter !== "all") params.jenis_kelamin = genderFilter;
        if (shoeTypeFilter !== "all") params.jenis_sepatu = shoeTypeFilter;
        if (shoeSizeFilter !== "all") params.ukuran_sepatu = shoeSizeFilter;
        if (kelompokJabatanFilter !== "all")
            params.kelompok_jabatan = kelompokJabatanFilter;

        console.log("Applying filters:", params); // Debug log

        router.visit(route("employees.index"), {
            data: params,
            preserveState: true,
            preserveScroll: true,
            onFinish: () => {
                setLoading(false);
                setTimeout(() => setIsNavigating(false), 300);
            },
            onError: (errors) => {
                console.error("Filter error:", errors);
                setLoading(false);
                setIsNavigating(false);
            },
        });
    };

    // FIXED: Enhanced search dengan debounce yang lebih robust
    const handleSearchChange = (value) => {
        setSearchQuery(value);

        console.log(`Search query changed: "${value}"`); // Debug log

        if (searchTimeout) {
            clearTimeout(searchTimeout);
        }

        const timeout = setTimeout(() => {
            console.log(`Executing search for: "${value}"`); // Debug log
            applyFilters(1); // Reset to page 1 on search
        }, 500);

        setSearchTimeout(timeout);
    };

    // FIXED: Enhanced handleFilterChange dengan validasi yang lebih baik
    const handleFilterChange = (filterType, value) => {
        console.log(`Filter changed: ${filterType} = ${value}`); // Debug log

        switch (filterType) {
            case "status":
                setStatusFilter(value);
                break;
            case "unit":
                setUnitFilter(value);
                // FIXED: Reset unit dan sub unit ketika unit organisasi berubah
                setUnitIdFilter("all");
                setSubUnitIdFilter("all");
                // FIXED: Update units dengan static data + API validation
                updateUnitsWithAPI(value);
                break;
            case "unitId":
                setUnitIdFilter(value);
                // FIXED: Reset sub unit ketika unit berubah
                setSubUnitIdFilter("all");
                // FIXED: Update sub units dengan static data + API validation
                updateSubUnitsWithAPI(value);
                break;
            case "subUnitId":
                setSubUnitIdFilter(value);
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
            case "kelompokJabatan":
                setKelompokJabatanFilter(value);
                break;
            default:
                console.warn(`Unknown filter type: ${filterType}`);
        }

        // Apply filters after a short delay to allow state updates
        setTimeout(() => {
            applyFilters(1); // Reset to page 1 on filter change
        }, 100);
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

    // FIXED: Clear all filters dengan reset yang lebih lengkap
    const clearAllFilters = () => {
        console.log("Clearing all filters"); // Debug log

        setSearchQuery("");
        setStatusFilter("all");
        setUnitFilter("all");
        setUnitIdFilter("all");
        setSubUnitIdFilter("all");
        setGenderFilter("all");
        setShoeTypeFilter("all");
        setShoeSizeFilter("all");
        setKelompokJabatanFilter("all");

        // Reset cascading data
        setAvailableUnits([]);
        setAvailableSubUnits([]);

        // Clear search timeout
        if (searchTimeout) {
            clearTimeout(searchTimeout);
        }

        router.visit(route("employees.index"), {
            preserveState: true,
            preserveScroll: true,
            onFinish: () => {
                console.log("All filters cleared successfully"); // Debug log
            },
        });
    };

    // FIXED: Enhanced hasActiveFilters check
    const hasActiveFilters = () => {
        const hasFilters =
            searchQuery.trim() ||
            statusFilter !== "all" ||
            unitFilter !== "all" ||
            unitIdFilter !== "all" ||
            subUnitIdFilter !== "all" ||
            genderFilter !== "all" ||
            shoeTypeFilter !== "all" ||
            shoeSizeFilter !== "all" ||
            kelompokJabatanFilter !== "all";

        console.log("Has active filters:", hasFilters); // Debug log
        return hasFilters;
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
        handleEmployeeClick(employee.id, "view");
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

    // UPDATED: Statistics from backend - dengan TAD Split dan support kelompok jabatan
    const stats = useMemo(() => {
        const data = employees.data || [];

        // Count new employees added today (simplified without timezone complexity)
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        const newToday = data.filter((employee) => {
            const createdAt = new Date(employee.created_at);
            createdAt.setHours(0, 0, 0, 0);
            return createdAt.getTime() === today.getTime();
        }).length;

        return {
            total: statistics.total || 0,
            pegawaiTetap: statistics.pegawaiTetap || 0,
            pkwt: statistics.pkwt || 0,
            // FITUR BARU: TAD dengan breakdown
            tadTotal: statistics.tad_total || statistics.tad || 0,
            tadPaketSDM: statistics.tad_paket_sdm || 0,
            tadPaketPekerjaan: statistics.tad_paket_pekerjaan || 0,
            uniqueUnits: statistics.uniqueUnits || 0,
            newToday: newToday,
        };
    }, [statistics, employees.data]);

    return (
        <DashboardLayout title={title}>
            <Head title="Management Karyawan - GAPURA ANGKASA SDM">
                <style>{`
                    /* Simplified CSS - No animations */

                    /* Clickable profile elements */
                    .profile-clickable {
                        cursor: pointer;
                        transition: all 0.3s ease;
                        border-radius: 6px;
                        padding: 2px 4px;
                    }

                    .profile-clickable:hover {
                        background-color: rgba(67, 148, 84, 0.1);
                        color: #439454;
                        transform: scale(1.02);
                    }

                    /* Extra small text for TAD breakdown */
                    .text-2xs {
                        font-size: 0.625rem;
                        line-height: 0.75rem;
                    }

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
                {/* Header Section - Compact */}
                <div className="sticky top-0 z-40 border-b border-gray-200 shadow-lg bg-white/95 backdrop-blur-sm">
                    <div className="px-6 py-5">
                        <div className="flex flex-col md:flex-row md:items-center md:justify-between">
                            <div className="space-y-1">
                                <h1 className="text-3xl font-bold tracking-tight text-gray-900">
                                    {title}
                                </h1>
                                <p className="font-medium text-gray-600">
                                    {subtitle}
                                </p>
                            </div>
                            <div className="flex gap-3 mt-3 md:mt-0">
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

                        {/* UPDATED: Statistics Cards dengan TAD Split - Compact Design */}
                        <div className="grid grid-cols-1 gap-3 mt-4 sm:grid-cols-2 lg:grid-cols-5">
                            <div className="relative p-4 overflow-hidden transition-all duration-300 border-2 border-blue-200 group bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl hover:border-blue-300 hover:shadow-lg hover:-translate-y-1">
                                <div className="absolute inset-0 transition-opacity duration-300 opacity-0 bg-gradient-to-br from-blue-400/10 to-blue-600/10 group-hover:opacity-100"></div>
                                <div className="relative flex items-center">
                                    <div className="p-2 transition-transform duration-300 rounded-lg shadow-md bg-gradient-to-br from-blue-500 to-blue-600 group-hover:scale-110">
                                        <Users className="w-5 h-5 text-white" />
                                    </div>
                                    <div className="ml-3">
                                        <p className="text-xs font-semibold text-blue-800">
                                            Total Karyawan
                                        </p>
                                        <p className="text-2xl font-bold text-blue-700 transition-transform duration-300 group-hover:scale-105">
                                            {stats.total}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div className="relative p-4 overflow-hidden transition-all duration-300 border-2 border-green-200 group bg-gradient-to-br from-green-50 to-green-100 rounded-xl hover:border-green-300 hover:shadow-lg hover:-translate-y-1">
                                <div className="absolute inset-0 transition-opacity duration-300 opacity-0 bg-gradient-to-br from-green-400/10 to-green-600/10 group-hover:opacity-100"></div>
                                <div className="relative flex items-center">
                                    <div className="p-2 transition-transform duration-300 shadow-md bg-gradient-to-br from-[#439454] to-green-600 rounded-lg group-hover:scale-110">
                                        <UserCheck className="w-5 h-5 text-white" />
                                    </div>
                                    <div className="ml-3">
                                        <p className="text-xs font-semibold text-green-800">
                                            Pegawai Tetap
                                        </p>
                                        <p className="text-2xl font-bold text-green-700 transition-transform duration-300 group-hover:scale-105">
                                            {stats.pegawaiTetap}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div className="relative p-4 overflow-hidden transition-all duration-300 border-2 border-blue-200 group bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl hover:border-blue-300 hover:shadow-lg hover:-translate-y-1">
                                <div className="absolute inset-0 transition-opacity duration-300 opacity-0 bg-gradient-to-br from-blue-400/10 to-blue-600/10 group-hover:opacity-100"></div>
                                <div className="relative flex items-center">
                                    <div className="p-2 transition-transform duration-300 rounded-lg shadow-md bg-gradient-to-br from-blue-500 to-blue-600 group-hover:scale-110">
                                        <Calendar className="w-5 h-5 text-white" />
                                    </div>
                                    <div className="ml-3">
                                        <p className="text-xs font-semibold text-blue-800">
                                            PKWT
                                        </p>
                                        <p className="text-2xl font-bold text-blue-700 transition-transform duration-300 group-hover:scale-105">
                                            {stats.pkwt}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            {/* FITUR BARU: TAD Card dengan Breakdown - Compact */}
                            <div className="relative p-4 overflow-hidden transition-all duration-300 border-2 border-orange-200 group bg-gradient-to-br from-orange-50 to-orange-100 rounded-xl hover:border-orange-300 hover:shadow-lg hover:-translate-y-1">
                                <div className="absolute inset-0 transition-opacity duration-300 opacity-0 bg-gradient-to-br from-orange-400/10 to-orange-600/10 group-hover:opacity-100"></div>
                                <div className="relative">
                                    <div className="flex items-center mb-1">
                                        <div className="p-2 transition-transform duration-300 rounded-lg shadow-md bg-gradient-to-br from-orange-500 to-orange-600 group-hover:scale-110">
                                            <Clock className="w-5 h-5 text-white" />
                                        </div>
                                        <div className="ml-3">
                                            <p className="text-xs font-semibold text-orange-800">
                                                TAD
                                            </p>
                                            <p className="text-2xl font-bold text-orange-700 transition-transform duration-300 group-hover:scale-105">
                                                {stats.tadTotal}
                                            </p>
                                        </div>
                                    </div>
                                    <div className="space-y-0.5 text-2xs text-orange-700">
                                        <div className="flex justify-between">
                                            <span>Paket SDM:</span>
                                            <span className="font-semibold">
                                                {stats.tadPaketSDM}
                                            </span>
                                        </div>
                                        <div className="flex justify-between">
                                            <span>Paket Pekerjaan:</span>
                                            <span className="font-semibold">
                                                {stats.tadPaketPekerjaan}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div className="relative p-4 overflow-hidden transition-all duration-300 border-2 border-purple-200 group bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl hover:border-purple-300 hover:shadow-lg hover:-translate-y-1">
                                <div className="absolute inset-0 transition-opacity duration-300 opacity-0 bg-gradient-to-br from-purple-400/10 to-purple-600/10 group-hover:opacity-100"></div>
                                <div className="relative flex items-center">
                                    <div
                                        className={`p-2 transition-transform duration-300 shadow-md rounded-lg group-hover:scale-110 ${
                                            stats.newToday > 0
                                                ? "bg-gradient-to-br from-orange-500 to-orange-600"
                                                : "bg-gradient-to-br from-purple-500 to-purple-600"
                                        }`}
                                    >
                                        {stats.newToday > 0 ? (
                                            <Star className="w-5 h-5 text-white" />
                                        ) : (
                                            <Building2 className="w-5 h-5 text-white" />
                                        )}
                                    </div>
                                    <div className="ml-3">
                                        <p
                                            className={`text-xs font-semibold ${
                                                stats.newToday > 0
                                                    ? "text-orange-800"
                                                    : "text-purple-800"
                                            }`}
                                        >
                                            {stats.newToday > 0
                                                ? "Baru Hari Ini"
                                                : "Unit Organisasi"}
                                        </p>
                                        <p
                                            className={`text-2xl font-bold transition-transform duration-300 group-hover:scale-105 ${
                                                stats.newToday > 0
                                                    ? "text-orange-700"
                                                    : "text-purple-700"
                                            }`}
                                        >
                                            {stats.newToday > 0
                                                ? stats.newToday
                                                : stats.uniqueUnits}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Enhanced Search and Filter Section - Compact */}
                <div className="px-6 py-5 border-b border-gray-200 bg-white/80 backdrop-blur-sm">
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

                    {/* IMPROVED: Enhanced Advanced Filter Section dengan struktur organisasi yang lebih baik */}
                    <div
                        className={`overflow-hidden transition-all duration-500 ease-in-out ${
                            showFilters
                                ? "max-h-[600px] opacity-100"
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
                                <div className="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-6">
                                    {/* Status Pegawai Filter */}
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
                                                <option value="PKWT">
                                                    PKWT
                                                </option>
                                                <option value="TAD PAKET SDM">
                                                    TAD Paket SDM
                                                </option>
                                                <option value="TAD PAKET PEKERJAAN">
                                                    TAD Paket Pekerjaan
                                                </option>
                                            </select>
                                            <ChevronDown className="absolute right-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400 group-hover:text-[#439454] transition-all duration-300 pointer-events-none group-hover:scale-110" />
                                        </div>
                                    </div>

                                    {/* Kelompok Jabatan Filter */}
                                    <div className="relative group">
                                        <label className="block mb-3 text-sm font-semibold text-gray-700 group-hover:text-[#439454] transition-colors duration-300">
                                            Kelompok Jabatan
                                        </label>
                                        <div className="relative">
                                            <div className="absolute inset-0 bg-gradient-to-r from-[#439454]/5 to-[#367a41]/5 rounded-xl opacity-0 group-hover:opacity-100 transition-all duration-300"></div>
                                            <select
                                                value={kelompokJabatanFilter}
                                                onChange={(e) =>
                                                    handleFilterChange(
                                                        "kelompokJabatan",
                                                        e.target.value
                                                    )
                                                }
                                                className="relative w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:ring-4 focus:ring-[#439454]/20 focus:border-[#439454] hover:border-[#439454]/60 hover:shadow-md transition-all duration-300 bg-white font-medium appearance-none cursor-pointer transform hover:scale-[1.02] focus:scale-[1.02]"
                                                style={{
                                                    backgroundImage: "none",
                                                }}
                                            >
                                                <option value="all">
                                                    Semua Kelompok
                                                </option>
                                                <option value="SUPERVISOR">
                                                    Supervisor
                                                </option>
                                                <option value="STAFF">
                                                    Staff
                                                </option>
                                                <option value="MANAGER">
                                                    Manager
                                                </option>
                                                <option value="EXECUTIVE GENERAL MANAGER">
                                                    Executive General Manager
                                                </option>
                                                <option value="ACCOUNT EXECUTIVE/AE">
                                                    Account Executive/AE
                                                </option>
                                            </select>
                                            <ChevronDown className="absolute right-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400 group-hover:text-[#439454] transition-all duration-300 pointer-events-none group-hover:scale-110" />
                                        </div>
                                    </div>

                                    {/* Jenis Kelamin Filter */}
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
                                                    Semua Jenis
                                                </option>
                                                <option value="L">
                                                    Laki-laki
                                                </option>
                                                <option value="P">
                                                    Perempuan
                                                </option>
                                            </select>
                                            <ChevronDown className="absolute right-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400 group-hover:text-[#439454] transition-all duration-300 pointer-events-none group-hover:scale-110" />
                                        </div>
                                    </div>

                                    {/* Jenis Sepatu Filter */}
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
                                                <option value="Pantofel">
                                                    Pantofel
                                                </option>
                                                <option value="Safety Shoes">
                                                    Safety Shoes
                                                </option>
                                            </select>
                                            <ChevronDown className="absolute right-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400 group-hover:text-[#439454] transition-all duration-300 pointer-events-none group-hover:scale-110" />
                                        </div>
                                    </div>

                                    {/* Ukuran Sepatu Filter */}
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
                                                {[
                                                    "36",
                                                    "37",
                                                    "38",
                                                    "39",
                                                    "40",
                                                    "41",
                                                    "42",
                                                    "43",
                                                    "44",
                                                ].map((size) => (
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

                                    {/* Seragam Filter - KOSONG sesuai permintaan */}
                                    <div className="relative opacity-50 group">
                                        <label className="block mb-3 text-sm font-semibold text-gray-500 transition-colors duration-300">
                                            Seragam
                                        </label>
                                        <div className="relative">
                                            <select
                                                disabled
                                                className="relative w-full px-4 py-3 font-medium bg-gray-100 border-2 border-gray-200 appearance-none cursor-not-allowed rounded-xl"
                                                style={{
                                                    backgroundImage: "none",
                                                }}
                                            >
                                                <option value="">
                                                    Belum Tersedia
                                                </option>
                                            </select>
                                            <ChevronDown className="absolute w-5 h-5 text-gray-300 transform -translate-y-1/2 pointer-events-none right-3 top-1/2" />
                                        </div>
                                    </div>
                                </div>

                                {/* IMPROVED: Unit Organisasi Section - tanpa header besar dan menggunakan static data */}
                                <div className="grid grid-cols-1 gap-6 mt-4 md:grid-cols-3">
                                    {/* Unit Organisasi Filter */}
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
                                                    Semua Unit Organisasi
                                                </option>
                                                <option value="EGM">EGM</option>
                                                <option value="GM">GM</option>
                                                <option value="Airside">
                                                    Airside
                                                </option>
                                                <option value="Landside">
                                                    Landside
                                                </option>
                                                <option value="Back Office">
                                                    Back Office
                                                </option>
                                                <option value="SSQC">
                                                    SSQC
                                                </option>
                                                <option value="Ancillary">
                                                    Ancillary
                                                </option>
                                            </select>
                                            <ChevronDown className="absolute right-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400 group-hover:text-[#439454] transition-all duration-300 pointer-events-none group-hover:scale-110" />
                                        </div>
                                    </div>

                                    {/* Unit Filter - Disabled hingga unit organisasi dipilih */}
                                    <div className="relative group">
                                        <label
                                            className={`block mb-3 text-sm font-semibold transition-colors duration-300 ${
                                                unitFilter === "all"
                                                    ? "text-gray-500"
                                                    : "text-gray-700 group-hover:text-[#439454]"
                                            }`}
                                        >
                                            Unit
                                        </label>
                                        <div className="relative">
                                            <div
                                                className={`absolute inset-0 bg-gradient-to-r from-[#439454]/5 to-[#367a41]/5 rounded-xl opacity-0 transition-all duration-300 ${
                                                    unitFilter !== "all"
                                                        ? "group-hover:opacity-100"
                                                        : ""
                                                }`}
                                            ></div>
                                            <select
                                                value={unitIdFilter}
                                                onChange={(e) =>
                                                    handleFilterChange(
                                                        "unitId",
                                                        e.target.value
                                                    )
                                                }
                                                disabled={unitFilter === "all"}
                                                className={`relative w-full px-4 py-3 border-2 rounded-xl focus:ring-4 focus:ring-[#439454]/20 focus:border-[#439454] font-medium appearance-none transition-all duration-300 transform ${
                                                    unitFilter === "all"
                                                        ? "border-gray-200 bg-gray-100 text-gray-500 cursor-not-allowed"
                                                        : "border-gray-300 bg-white text-gray-900 cursor-pointer hover:border-[#439454]/60 hover:shadow-md hover:scale-[1.02] focus:scale-[1.02]"
                                                }`}
                                                style={{
                                                    backgroundImage: "none",
                                                }}
                                            >
                                                <option value="all">
                                                    {unitFilter === "all"
                                                        ? "Pilih Unit Organisasi Dulu"
                                                        : "Semua Unit"}
                                                </option>
                                                {availableUnits.map((unit) => (
                                                    <option
                                                        key={unit}
                                                        value={unit}
                                                    >
                                                        {unit}
                                                    </option>
                                                ))}
                                            </select>
                                            <ChevronDown
                                                className={`absolute right-3 top-1/2 transform -translate-y-1/2 w-5 h-5 pointer-events-none transition-all duration-300 ${
                                                    unitFilter === "all"
                                                        ? "text-gray-300"
                                                        : "text-gray-400 group-hover:text-[#439454] group-hover:scale-110"
                                                }`}
                                            />
                                        </div>
                                    </div>

                                    {/* Sub Unit Filter - Disabled hingga unit dipilih dan unit bukan EGM/GM */}
                                    <div className="relative group">
                                        <label
                                            className={`block mb-3 text-sm font-semibold transition-colors duration-300 ${
                                                unitIdFilter === "all" ||
                                                unitIdFilter === "EGM" ||
                                                unitIdFilter === "GM"
                                                    ? "text-gray-500"
                                                    : "text-gray-700 group-hover:text-[#439454]"
                                            }`}
                                        >
                                            Sub Unit
                                        </label>
                                        <div className="relative">
                                            <div
                                                className={`absolute inset-0 bg-gradient-to-r from-[#439454]/5 to-[#367a41]/5 rounded-xl opacity-0 transition-all duration-300 ${
                                                    unitIdFilter !== "all" &&
                                                    unitIdFilter !== "EGM" &&
                                                    unitIdFilter !== "GM" &&
                                                    availableSubUnits.length > 0
                                                        ? "group-hover:opacity-100"
                                                        : ""
                                                }`}
                                            ></div>
                                            <select
                                                value={subUnitIdFilter}
                                                onChange={(e) =>
                                                    handleFilterChange(
                                                        "subUnitId",
                                                        e.target.value
                                                    )
                                                }
                                                disabled={
                                                    unitIdFilter === "all" ||
                                                    unitIdFilter === "EGM" ||
                                                    unitIdFilter === "GM" ||
                                                    availableSubUnits.length ===
                                                        0
                                                }
                                                className={`relative w-full px-4 py-3 border-2 rounded-xl focus:ring-4 focus:ring-[#439454]/20 focus:border-[#439454] font-medium appearance-none transition-all duration-300 transform ${
                                                    unitIdFilter === "all" ||
                                                    unitIdFilter === "EGM" ||
                                                    unitIdFilter === "GM" ||
                                                    availableSubUnits.length ===
                                                        0
                                                        ? "border-gray-200 bg-gray-100 text-gray-500 cursor-not-allowed"
                                                        : "border-gray-300 bg-white text-gray-900 cursor-pointer hover:border-[#439454]/60 hover:shadow-md hover:scale-[1.02] focus:scale-[1.02]"
                                                }`}
                                                style={{
                                                    backgroundImage: "none",
                                                }}
                                            >
                                                <option value="all">
                                                    {unitIdFilter === "all"
                                                        ? "Pilih Unit Dulu"
                                                        : unitIdFilter ===
                                                              "EGM" ||
                                                          unitIdFilter === "GM"
                                                        ? "Tidak Ada Sub Unit"
                                                        : availableSubUnits.length ===
                                                          0
                                                        ? "Tidak Ada Sub Unit"
                                                        : "Semua Sub Unit"}
                                                </option>
                                                {availableSubUnits.map(
                                                    (subUnit) => (
                                                        <option
                                                            key={subUnit}
                                                            value={subUnit}
                                                        >
                                                            {subUnit}
                                                        </option>
                                                    )
                                                )}
                                            </select>
                                            <ChevronDown
                                                className={`absolute right-3 top-1/2 transform -translate-y-1/2 w-5 h-5 pointer-events-none transition-all duration-300 ${
                                                    unitIdFilter === "all" ||
                                                    unitIdFilter === "EGM" ||
                                                    unitIdFilter === "GM" ||
                                                    availableSubUnits.length ===
                                                        0
                                                        ? "text-gray-300"
                                                        : "text-gray-400 group-hover:text-[#439454] group-hover:scale-110"
                                                }`}
                                            />
                                        </div>
                                    </div>
                                </div>

                                {/* Clear All Filters Button */}
                                {hasActiveFilters() && (
                                    <div className="flex justify-end mt-6">
                                        <button
                                            onClick={clearAllFilters}
                                            className="flex items-center gap-2 px-6 py-3 text-sm font-semibold text-gray-600 transition-all duration-300 transform border-2 border-gray-300 rounded-xl hover:border-red-300 hover:text-red-600 hover:bg-red-50 focus:ring-4 focus:ring-red-100 hover:scale-105"
                                        >
                                            <X className="w-4 h-4" />
                                            Clear All Filters
                                        </button>
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>

                    {/* ENHANCED: Active Filters Display dengan Unit dan Sub Unit */}
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
                                {kelompokJabatanFilter !== "all" && (
                                    <span className="inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold transition-all duration-300 transform border rounded-full text-emerald-800 bg-emerald-100 border-emerald-200 group hover:bg-emerald-200 hover:scale-105">
                                        Kelompok: {kelompokJabatanFilter}
                                        <button
                                            onClick={() =>
                                                removeFilter("kelompokJabatan")
                                            }
                                            className="transition-colors duration-300 text-emerald-600 hover:text-red-600"
                                        >
                                            <X className="w-3 h-3" />
                                        </button>
                                    </span>
                                )}
                                {unitFilter !== "all" && (
                                    <span className="inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold text-blue-800 transition-all duration-300 transform bg-blue-100 border border-blue-200 rounded-full group hover:bg-blue-200 hover:scale-105">
                                        Unit Org: {unitFilter}
                                        <button
                                            onClick={() => removeFilter("unit")}
                                            className="text-blue-600 transition-colors duration-300 hover:text-red-600"
                                        >
                                            <X className="w-3 h-3" />
                                        </button>
                                    </span>
                                )}
                                {unitIdFilter !== "all" && (
                                    <span className="inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold text-purple-800 transition-all duration-300 transform bg-purple-100 border border-purple-200 rounded-full group hover:bg-purple-200 hover:scale-105">
                                        Unit: {unitIdFilter}
                                        <button
                                            onClick={() =>
                                                removeFilter("unitId")
                                            }
                                            className="text-purple-600 transition-colors duration-300 hover:text-red-600"
                                        >
                                            <X className="w-3 h-3" />
                                        </button>
                                    </span>
                                )}
                                {subUnitIdFilter !== "all" && (
                                    <span className="inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold text-indigo-800 transition-all duration-300 transform bg-indigo-100 border border-indigo-200 rounded-full group hover:bg-indigo-200 hover:scale-105">
                                        Sub Unit: {subUnitIdFilter}
                                        <button
                                            onClick={() =>
                                                removeFilter("subUnitId")
                                            }
                                            className="text-indigo-600 transition-colors duration-300 hover:text-red-600"
                                        >
                                            <X className="w-3 h-3" />
                                        </button>
                                    </span>
                                )}
                                {genderFilter !== "all" && (
                                    <span className="inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold text-orange-800 transition-all duration-300 transform bg-orange-100 border border-orange-200 rounded-full group hover:bg-orange-200 hover:scale-105">
                                        Gender:{" "}
                                        {genderFilter === "L"
                                            ? "Laki-laki"
                                            : "Perempuan"}
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
                                    <span className="inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold text-yellow-800 transition-all duration-300 transform bg-yellow-100 border border-yellow-200 rounded-full group hover:bg-yellow-200 hover:scale-105">
                                        Sepatu: {shoeTypeFilter}
                                        <button
                                            onClick={() =>
                                                removeFilter("shoeType")
                                            }
                                            className="text-yellow-600 transition-colors duration-300 hover:text-red-600"
                                        >
                                            <X className="w-3 h-3" />
                                        </button>
                                    </span>
                                )}
                                {shoeSizeFilter !== "all" && (
                                    <span className="inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold text-gray-800 transition-all duration-300 transform bg-gray-100 border border-gray-200 rounded-full group hover:bg-gray-200 hover:scale-105">
                                        Ukuran: {shoeSizeFilter}
                                        <button
                                            onClick={() =>
                                                removeFilter("shoeSize")
                                            }
                                            className="text-gray-600 transition-colors duration-300 hover:text-red-600"
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

                {/* UPDATED: Enhanced Employee Table dengan Status Pegawai Split dan Kelompok Jabatan - Compact */}
                <div className="px-6 pb-6">
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
                                                Kelompok Jabatan
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
                                            (employee, index) => {
                                                // Enhanced check if this is a new employee
                                                const isNew =
                                                    isNewEmployee(employee);

                                                return (
                                                    <tr
                                                        key={
                                                            employee.id ||
                                                            employee.nip ||
                                                            index
                                                        }
                                                        data-employee-id={
                                                            employee.id
                                                        }
                                                        className="group transition-all duration-300 hover:bg-gradient-to-r hover:from-[#439454]/5 hover:to-[#367a41]/5"
                                                    >
                                                        {/* No Column */}
                                                        <td
                                                            className="px-6 py-5 text-sm font-bold text-gray-900 whitespace-nowrap group-hover:text-[#439454] transition-colors duration-300 profile-clickable"
                                                            onClick={(e) =>
                                                                handleEmployeeProfileClick(
                                                                    employee.id,
                                                                    e
                                                                )
                                                            }
                                                            title="Klik untuk menyembunyikan label baru"
                                                        >
                                                            {(pagination.current_page -
                                                                1) *
                                                                pagination.per_page +
                                                                index +
                                                                1}
                                                        </td>

                                                        {/* NIP Column dengan label hanya di atas profile photo */}
                                                        <td className="px-6 py-5 whitespace-nowrap">
                                                            <div className="flex items-center">
                                                                <div className="relative flex-shrink-0 w-10 h-10">
                                                                    <SimplifiedNewEmployeeLabel
                                                                        employee={
                                                                            employee
                                                                        }
                                                                    />
                                                                    <div
                                                                        className="w-10 h-10 bg-gradient-to-br from-[#439454] to-[#367a41] rounded-full flex items-center justify-center text-white text-sm font-bold shadow-lg group-hover:scale-110 transition-transform duration-300 profile-clickable"
                                                                        onClick={(
                                                                            e
                                                                        ) =>
                                                                            handleEmployeeProfileClick(
                                                                                employee.id,
                                                                                e
                                                                            )
                                                                        }
                                                                        title="Klik untuk menyembunyikan label baru"
                                                                    >
                                                                        {getInitials(
                                                                            employee.nama_lengkap
                                                                        )}
                                                                    </div>
                                                                </div>
                                                                <div className="ml-4">
                                                                    <div className="flex flex-col">
                                                                        <div
                                                                            className="text-sm font-bold text-gray-900 group-hover:text-[#439454] transition-colors duration-300 profile-clickable"
                                                                            onClick={(
                                                                                e
                                                                            ) =>
                                                                                handleEmployeeProfileClick(
                                                                                    employee.id,
                                                                                    e
                                                                                )
                                                                            }
                                                                            title="Klik untuk menyembunyikan label baru"
                                                                        >
                                                                            {employee.nip ||
                                                                                "-"}
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </td>

                                                        {/* Nama Lengkap Column */}
                                                        <td className="px-6 py-5 whitespace-nowrap">
                                                            <div
                                                                className="text-sm font-bold text-gray-900 group-hover:text-[#439454] transition-colors duration-300 profile-clickable"
                                                                onClick={(e) =>
                                                                    handleEmployeeProfileClick(
                                                                        employee.id,
                                                                        e
                                                                    )
                                                                }
                                                                title="Klik untuk menyembunyikan label baru"
                                                            >
                                                                {employee.nama_lengkap ||
                                                                    "-"}
                                                            </div>
                                                            <div
                                                                className="text-sm font-medium text-gray-500 profile-clickable"
                                                                onClick={(e) =>
                                                                    handleEmployeeProfileClick(
                                                                        employee.id,
                                                                        e
                                                                    )
                                                                }
                                                                title="Klik untuk menyembunyikan label baru"
                                                            >
                                                                {employee.nama_jabatan ||
                                                                    employee.jabatan ||
                                                                    "-"}
                                                            </div>
                                                        </td>

                                                        {/* UPDATED: Status Pegawai Column dengan TAD Split */}
                                                        <td className="px-6 py-5 whitespace-nowrap">
                                                            <span
                                                                className={`inline-flex px-3 py-2 text-xs font-bold rounded-full shadow-sm transition-all duration-300 group-hover:scale-105 profile-clickable ${
                                                                    employee.status_pegawai ===
                                                                    "PEGAWAI TETAP"
                                                                        ? "bg-gradient-to-r from-green-100 to-green-200 text-green-800 border border-green-300"
                                                                        : employee.status_pegawai ===
                                                                          "PKWT"
                                                                        ? "bg-gradient-to-r from-blue-100 to-blue-200 text-blue-800 border border-blue-300"
                                                                        : employee.status_pegawai ===
                                                                          "TAD PAKET SDM"
                                                                        ? "bg-gradient-to-r from-yellow-100 to-yellow-200 text-yellow-800 border border-yellow-300"
                                                                        : employee.status_pegawai ===
                                                                          "TAD PAKET PEKERJAAN"
                                                                        ? "bg-gradient-to-r from-orange-100 to-orange-200 text-orange-800 border border-orange-300"
                                                                        : "bg-gradient-to-r from-gray-100 to-gray-200 text-gray-800 border border-gray-300"
                                                                }`}
                                                                onClick={(e) =>
                                                                    handleEmployeeProfileClick(
                                                                        employee.id,
                                                                        e
                                                                    )
                                                                }
                                                                title="Klik untuk menyembunyikan label baru"
                                                            >
                                                                {employee.status_pegawai ||
                                                                    "-"}
                                                            </span>
                                                        </td>

                                                        {/* FITUR BARU: Kelompok Jabatan Column */}
                                                        <td className="px-6 py-5 whitespace-nowrap">
                                                            <span
                                                                className={`inline-flex px-3 py-2 text-xs font-bold rounded-full shadow-sm transition-all duration-300 group-hover:scale-105 profile-clickable ${
                                                                    employee.kelompok_jabatan ===
                                                                    "MANAGER"
                                                                        ? "bg-gradient-to-r from-purple-100 to-purple-200 text-purple-800 border border-purple-300"
                                                                        : employee.kelompok_jabatan ===
                                                                          "SUPERVISOR"
                                                                        ? "bg-gradient-to-r from-indigo-100 to-indigo-200 text-indigo-800 border border-indigo-300"
                                                                        : employee.kelompok_jabatan ===
                                                                          "STAFF"
                                                                        ? "bg-gradient-to-r from-blue-100 to-blue-200 text-blue-800 border border-blue-300"
                                                                        : employee.kelompok_jabatan ===
                                                                          "EXECUTIVE GENERAL MANAGER"
                                                                        ? "bg-gradient-to-r from-red-100 to-red-200 text-red-800 border border-red-300"
                                                                        : employee.kelompok_jabatan ===
                                                                          "ACCOUNT EXECUTIVE/AE"
                                                                        ? "bg-gradient-to-r from-green-100 to-green-200 text-green-800 border border-green-300"
                                                                        : "bg-gradient-to-r from-gray-100 to-gray-200 text-gray-800 border border-gray-300"
                                                                }`}
                                                                onClick={(e) =>
                                                                    handleEmployeeProfileClick(
                                                                        employee.id,
                                                                        e
                                                                    )
                                                                }
                                                                title="Klik untuk menyembunyikan label baru"
                                                            >
                                                                {employee.kelompok_jabatan ||
                                                                    "-"}
                                                            </span>
                                                        </td>

                                                        <td
                                                            className="px-6 py-5 text-sm font-semibold text-gray-900 whitespace-nowrap group-hover:text-[#439454] transition-colors duration-300 profile-clickable"
                                                            onClick={(e) =>
                                                                handleEmployeeProfileClick(
                                                                    employee.id,
                                                                    e
                                                                )
                                                            }
                                                            title="Klik untuk menyembunyikan label baru"
                                                        >
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
                                                                    onClick={() =>
                                                                        handleEmployeeClick(
                                                                            employee.id,
                                                                            "edit"
                                                                        )
                                                                    }
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
                                                                            handleEmployeeClick(
                                                                                employee.id,
                                                                                "delete"
                                                                            );
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
                                                );
                                            }
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
