import React, { useState } from "react";
import {
    X,
    User,
    Building2,
    GraduationCap,
    FileText,
    MapPin,
    Calendar,
    Phone,
    Mail,
    Shield,
    Users,
    UserCheck,
    Briefcase,
    Clock,
    Star,
    CreditCard,
    Ruler,
    Weight,
    Heart,
    Building,
    Trash2,
    AlertTriangle,
    Loader2,
} from "lucide-react";
import axios from "axios";

const EmployeeDetailModal = ({
    employee,
    isOpen,
    onClose,
    onEmployeeDeleted,
}) => {
    const [showDeleteConfirm, setShowDeleteConfirm] = useState(false);
    const [deleteLoading, setDeleteLoading] = useState(false);

    if (!isOpen || !employee) return null;

    // UPDATED: Helper function untuk get unit name dengan format kode dari controller
    const getUnitName = () => {
        // Priority 1: Gunakan data unit yang sudah diformat dari controller jika tersedia
        if (employee.unit_display_formatted) {
            // Extract unit name portion dari format "(XX) Nama Unit - Nama Organisasi"
            const formatted = employee.unit_display_formatted;
            if (formatted.includes(" - ")) {
                const parts = formatted.split(" - ");
                if (parts.length > 1) {
                    return parts[0]; // Return "(XX) Nama Unit" part
                }
            }
            return formatted;
        }

        // Priority 2: Gunakan kode_organisasi untuk mapping jika tersedia
        if (employee.kode_organisasi) {
            const unitDisplayMapping = {
                EGM: "EGM",
                GM: "GM",
                MO: "(MO) Movement Operations",
                ME: "(ME) Maintenance Equipment",
                MF: "(MF) Movement Flight",
                MS: "(MS) Movement Service",
                MU: "(MU) Management Unit",
                MK: "(MK) Management Keuangan",
                MQ: "(MQ) Management Quality",
                MB: "(MB) Management Business",
            };

            const formatted = unitDisplayMapping[employee.kode_organisasi];
            if (formatted) {
                return formatted;
            }
        }

        // Priority 3: Fallback ke relasi database dengan format kode
        if (employee.unit && employee.unit.name) {
            // Coba format dengan kode jika ada unit_organisasi
            if (employee.unit_organisasi) {
                const unitCodeMapping = {
                    Airside: {
                        "Movement Operations": "MO",
                        "Maintenance Equipment": "ME",
                    },
                    Landside: {
                        "Movement Flight": "MF",
                        "Movement Service": "MS",
                    },
                    "Back Office": {
                        "Management Keuangan": "MK",
                        "Management Unit": "MU",
                    },
                    SSQC: {
                        "Management Quality": "MQ",
                    },
                    Ancillary: {
                        "Management Business": "MB",
                    },
                };

                if (
                    unitCodeMapping[employee.unit_organisasi] &&
                    unitCodeMapping[employee.unit_organisasi][
                        employee.unit.name
                    ]
                ) {
                    const code =
                        unitCodeMapping[employee.unit_organisasi][
                            employee.unit.name
                        ];
                    return `(${code}) ${employee.unit.name}`;
                }
            }
            return employee.unit.name;
        }

        // Priority 4: Fallback ke unit_id atau default
        return employee.unit_id || "-";
    };

    // UPDATED: Helper function untuk get sub unit name dengan format yang konsisten
    const getSubUnitName = () => {
        // Priority 1: Gunakan relasi database jika tersedia
        if (employee.sub_unit && employee.sub_unit.name) {
            return employee.sub_unit.name;
        }

        // Priority 2: Check if unit organisasi has no sub units (EGM, GM)
        if (["EGM", "GM"].includes(employee.unit_organisasi)) {
            return "-";
        }

        // Priority 3: Fallback ke sub_unit_id atau default
        return employee.sub_unit_id || "-";
    };

    // UPDATED: Helper function untuk get unit organisasi dengan format kode
    const getUnitOrganisasiFormatted = () => {
        // Priority 1: Gunakan data yang sudah diformat dari controller
        if (employee.unit_organisasi_formatted) {
            return employee.unit_organisasi_formatted;
        }

        // Priority 2: Gunakan kode_organisasi untuk mapping
        if (employee.kode_organisasi) {
            const unitDisplayMapping = {
                EGM: "EGM",
                GM: "GM",
                MO: "(MO) Movement Operations",
                ME: "(ME) Maintenance Equipment",
                MF: "(MF) Movement Flight",
                MS: "(MS) Movement Service",
                MU: "(MU) Management Unit",
                MK: "(MK) Management Keuangan",
                MQ: "(MQ) Management Quality",
                MB: "(MB) Management Business",
            };

            const formatted = unitDisplayMapping[employee.kode_organisasi];
            if (formatted) {
                return formatted;
            }
        }

        // Priority 3: Fallback ke unit_organisasi original
        return employee.unit_organisasi || "-";
    };

    // UPDATED: Helper function untuk format display struktur organisasi lengkap dengan kode
    const getOrganizationStructure = () => {
        const parts = [];

        // Gunakan unit organisasi yang sudah diformat dengan kode
        const unitOrganisasi = getUnitOrganisasiFormatted();
        if (unitOrganisasi && unitOrganisasi !== "-") {
            parts.push(unitOrganisasi);
        }

        // Gunakan unit name yang sudah diformat dengan kode
        const unitName = getUnitName();
        if (
            unitName &&
            unitName !== "-" &&
            !unitOrganisasi.includes(unitName)
        ) {
            parts.push(unitName);
        }

        // Gunakan sub unit name
        const subUnitName = getSubUnitName();
        if (subUnitName && subUnitName !== "-") {
            parts.push(subUnitName);
        }

        return parts.join(" → ");
    };

    const formatDate = (dateString) => {
        if (!dateString) return "-";
        try {
            return new Date(dateString).toLocaleDateString("id-ID", {
                day: "2-digit",
                month: "long",
                year: "numeric",
            });
        } catch {
            return "-";
        }
    };

    // ENHANCED: Fungsi calculateMasaKerja yang lebih robust dengan timezone support
    const calculateMasaKerja = (tmtMulaiKerja, tmtBerakhirKerja = null) => {
        if (!tmtMulaiKerja || tmtMulaiKerja === "" || tmtMulaiKerja === null) {
            console.log(
                "calculateMasaKerja: Empty or null tmt_mulai_kerja",
                tmtMulaiKerja
            );
            return "-";
        }

        try {
            // Enhanced date parsing dengan multiple format support
            let startDate, endDate;

            // Parse start date dengan berbagai format
            if (typeof tmtMulaiKerja === "string") {
                // Normalize date string format
                const normalizedStart = tmtMulaiKerja.includes("T")
                    ? tmtMulaiKerja.split("T")[0]
                    : tmtMulaiKerja;
                startDate = new Date(normalizedStart + "T00:00:00.000Z");
            } else {
                startDate = new Date(tmtMulaiKerja);
            }

            // Parse end date atau gunakan tanggal sekarang dengan timezone WITA
            if (
                tmtBerakhirKerja &&
                tmtBerakhirKerja !== "" &&
                tmtBerakhirKerja !== null
            ) {
                if (typeof tmtBerakhirKerja === "string") {
                    const normalizedEnd = tmtBerakhirKerja.includes("T")
                        ? tmtBerakhirKerja.split("T")[0]
                        : tmtBerakhirKerja;
                    endDate = new Date(normalizedEnd + "T00:00:00.000Z");
                } else {
                    endDate = new Date(tmtBerakhirKerja);
                }
            } else {
                // Gunakan tanggal sekarang dengan timezone Asia/Makassar (WITA)
                const now = new Date();
                const witaOffset = 8 * 60; // WITA = UTC+8
                const utc = now.getTime() + now.getTimezoneOffset() * 60000;
                endDate = new Date(utc + witaOffset * 60000);
            }

            // Validate parsed dates
            if (isNaN(startDate.getTime())) {
                console.warn("calculateMasaKerja: Invalid start date", {
                    original: tmtMulaiKerja,
                    parsed: startDate,
                });
                return "Tanggal mulai tidak valid";
            }

            if (isNaN(endDate.getTime())) {
                console.warn("calculateMasaKerja: Invalid end date", {
                    original: tmtBerakhirKerja,
                    parsed: endDate,
                });
                return "Tanggal berakhir tidak valid";
            }

            // Check if end date is before start date
            if (endDate < startDate) {
                console.warn("calculateMasaKerja: End date before start date", {
                    startDate: startDate.toISOString(),
                    endDate: endDate.toISOString(),
                });
                return "Tanggal berakhir sebelum tanggal mulai";
            }

            // ENHANCED: Calculate using more accurate method
            let years = endDate.getFullYear() - startDate.getFullYear();
            let months = endDate.getMonth() - startDate.getMonth();
            let days = endDate.getDate() - startDate.getDate();

            // Adjust for negative days
            if (days < 0) {
                months--;
                // Get days in previous month
                const prevMonth = new Date(
                    endDate.getFullYear(),
                    endDate.getMonth(),
                    0
                );
                days += prevMonth.getDate();
            }

            // Adjust for negative months
            if (months < 0) {
                years--;
                months += 12;
            }

            // Enhanced debug logging
            console.log("calculateMasaKerja: Calculation details", {
                employee_nik: employee?.nik,
                startDate: startDate.toISOString(),
                endDate: endDate.toISOString(),
                years,
                months,
                days,
                totalDays: Math.floor(
                    (endDate - startDate) / (1000 * 60 * 60 * 24)
                ),
            });

            // Return formatted result dengan logika yang lebih baik
            if (years > 0 && months > 0) {
                return `${years} tahun ${months} bulan`;
            } else if (years > 0) {
                return `${years} tahun`;
            } else if (months > 0) {
                return `${months} bulan`;
            } else {
                // Check if at least some days exist
                const daysDiff = Math.floor(
                    (endDate - startDate) / (1000 * 60 * 60 * 24)
                );

                console.log("calculateMasaKerja: Less than 1 month", {
                    daysDiff,
                    startDate: startDate.toDateString(),
                    endDate: endDate.toDateString(),
                });

                if (daysDiff > 0) {
                    return "Kurang dari 1 bulan";
                } else {
                    return "Belum ada masa kerja";
                }
            }
        } catch (error) {
            console.error("calculateMasaKerja: Error in calculation", {
                error: error.message,
                tmtMulaiKerja,
                tmtBerakhirKerja,
                employee_nik: employee?.nik,
            });
            return "Error dalam perhitungan";
        }
    };

    // ENHANCED: Function untuk mendapatkan masa kerja dengan comprehensive fallback dan debugging
    const getMasaKerja = () => {
        // Enhanced debug logging untuk troubleshooting
        const debugData = {
            employee_id: employee?.id,
            employee_nik: employee?.nik,
            employee_name: employee?.nama_lengkap,
            masa_kerja_from_db: employee?.masa_kerja,
            tmt_mulai_kerja: employee?.tmt_mulai_kerja,
            tmt_berakhir_kerja: employee?.tmt_berakhir_kerja,
            masa_kerja_type: typeof employee?.masa_kerja,
            tmt_mulai_kerja_type: typeof employee?.tmt_mulai_kerja,
        };

        console.log("getMasaKerja: Debug data", debugData);

        // Priority 1: Use pre-calculated masa_kerja from database/controller
        if (
            employee?.masa_kerja &&
            employee.masa_kerja !== "" &&
            employee.masa_kerja !== "-" &&
            employee.masa_kerja !== null &&
            employee.masa_kerja !== undefined
        ) {
            console.log(
                "getMasaKerja: Using database masa_kerja",
                employee.masa_kerja
            );
            return employee.masa_kerja;
        }

        // Priority 2: Calculate from TMT dates
        if (
            employee?.tmt_mulai_kerja &&
            employee.tmt_mulai_kerja !== "" &&
            employee.tmt_mulai_kerja !== null &&
            employee.tmt_mulai_kerja !== undefined
        ) {
            const calculated = calculateMasaKerja(
                employee.tmt_mulai_kerja,
                employee.tmt_berakhir_kerja
            );

            console.log("getMasaKerja: Calculated from TMT dates", {
                calculated,
                tmt_mulai_kerja: employee.tmt_mulai_kerja,
                tmt_berakhir_kerja: employee.tmt_berakhir_kerja,
            });

            return calculated;
        }

        // Priority 3: Debug why no masa kerja available
        console.warn("getMasaKerja: No masa kerja available", {
            reason: !employee?.tmt_mulai_kerja
                ? "No TMT Mulai Kerja"
                : "Unknown",
            employee_data_keys: employee
                ? Object.keys(employee)
                : "No employee data",
        });

        return "-";
    };

    const getInitials = (name) => {
        if (!name) return "??";
        return name
            .split(" ")
            .map((n) => n[0])
            .join("")
            .toUpperCase()
            .slice(0, 2);
    };

    const formatGender = (gender) => {
        if (!gender) return "-";
        if (gender === "L" || gender === "Laki-laki") return "Laki-laki";
        if (gender === "P" || gender === "Perempuan") return "Perempuan";
        return gender;
    };

    const handleBackdropClick = (e) => {
        if (e.target === e.currentTarget) {
            onClose();
        }
    };

    // Handle delete employee
    const handleDeleteEmployee = async () => {
        if (!employee?.id) {
            console.error("No employee ID available for deletion");
            return;
        }

        setDeleteLoading(true);

        try {
            console.log("Attempting to delete employee:", {
                id: employee.id,
                nik: employee.nik,
                nama: employee.nama_lengkap,
            });

            // Send DELETE request to Laravel backend
            const response = await axios.delete(`/employees/${employee.id}`, {
                headers: {
                    "Content-Type": "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                },
            });

            console.log("Delete response:", response);

            if (response.status === 200 || response.status === 204) {
                // Success - close modals and notify parent
                setShowDeleteConfirm(false);
                onClose();

                // Call parent callback to refresh data and update statistics
                if (onEmployeeDeleted) {
                    onEmployeeDeleted({
                        success: true,
                        message: `Data karyawan ${employee.nama_lengkap} berhasil dihapus`,
                        deletedEmployee: employee,
                    });
                }

                console.log("Employee deleted successfully");
            } else {
                throw new Error(
                    `Unexpected response status: ${response.status}`
                );
            }
        } catch (error) {
            console.error("Delete employee error:", error);

            let errorMessage = "Terjadi kesalahan saat menghapus data karyawan";

            if (error.response) {
                // Server responded with error status
                console.error("Delete error response:", error.response);
                if (error.response.data?.message) {
                    errorMessage = error.response.data.message;
                } else if (error.response.status === 404) {
                    errorMessage = "Data karyawan tidak ditemukan";
                } else if (error.response.status === 403) {
                    errorMessage =
                        "Anda tidak memiliki izin untuk menghapus data ini";
                } else if (error.response.status >= 500) {
                    errorMessage =
                        "Terjadi kesalahan server. Silakan coba lagi.";
                }
            } else if (error.request) {
                // Request was made but no response received
                errorMessage =
                    "Tidak dapat menghubungi server. Periksa koneksi internet Anda.";
            }

            // Call parent callback to show error
            if (onEmployeeDeleted) {
                onEmployeeDeleted({
                    success: false,
                    message: errorMessage,
                    error: error,
                });
            }
        } finally {
            setDeleteLoading(false);
        }
    };

    const DetailCard = ({ title, icon: Icon, children, className = "" }) => (
        <div
            className={`p-6 space-y-5 transition-shadow duration-300 border border-gray-200 shadow-lg rounded-2xl bg-gradient-to-br from-white to-gray-50 hover:shadow-xl ${className}`}
        >
            <div className="flex items-center mb-4 space-x-3">
                <div className="p-3 bg-gradient-to-br from-[#439454] to-[#367a41] rounded-xl shadow-lg">
                    <Icon className="w-5 h-5 text-white" />
                </div>
                <h4 className="text-lg font-bold text-gray-900">{title}</h4>
            </div>
            {children}
        </div>
    );

    const FieldRow = ({ label, value, colSpan = 1 }) => (
        <div
            className={`space-y-2 ${
                colSpan === 2 ? "col-span-2" : colSpan === 3 ? "col-span-3" : ""
            }`}
        >
            <label className="text-sm font-semibold tracking-wide text-gray-600 uppercase">
                {label}
            </label>
            <p className="px-3 py-2 text-sm font-medium text-gray-900 bg-gray-100 rounded-lg">
                {value || "-"}
            </p>
        </div>
    );

    // Enhanced: Execute getMasaKerja dan log hasilnya untuk debugging
    const masaKerjaResult = getMasaKerja();
    console.log("EmployeeDetailModal: Final masa kerja result", {
        employee_nik: employee?.nik,
        masa_kerja: masaKerjaResult,
    });

    // UPDATED: Get formatted values for debugging
    const unitOrganisasiFormatted = getUnitOrganisasiFormatted();
    const unitNameFormatted = getUnitName();
    const subUnitNameFormatted = getSubUnitName();

    console.log("EmployeeDetailModal: Unit formatting debug", {
        employee_nik: employee?.nik,
        original_unit_organisasi: employee.unit_organisasi,
        unit_organisasi_formatted: unitOrganisasiFormatted,
        original_unit_name: employee.unit?.name,
        unit_name_formatted: unitNameFormatted,
        original_sub_unit_name: employee.sub_unit?.name,
        sub_unit_name_formatted: subUnitNameFormatted,
        kode_organisasi: employee.kode_organisasi,
        nama_organisasi: employee.nama_organisasi,
        unit_display_formatted_from_controller: employee.unit_display_formatted,
        unit_organisasi_formatted_from_controller:
            employee.unit_organisasi_formatted,
    });

    // Delete Confirmation Modal
    const DeleteConfirmationModal = () => {
        if (!showDeleteConfirm) return null;

        return (
            <div className="fixed inset-0 overflow-y-auto z-60 animate-fadeIn">
                <div className="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                    <div
                        className="fixed inset-0 transition-opacity bg-black/60 backdrop-blur-sm"
                        aria-hidden="true"
                    ></div>

                    <div className="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white shadow-2xl rounded-2xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full animate-scaleIn">
                        {/* Header */}
                        <div className="px-6 py-4 bg-gradient-to-r from-red-500 to-red-600">
                            <div className="flex items-center space-x-3">
                                <div className="p-2 rounded-lg bg-white/20">
                                    <AlertTriangle className="w-6 h-6 text-white" />
                                </div>
                                <h3 className="text-lg font-bold text-white">
                                    Konfirmasi Hapus Data
                                </h3>
                            </div>
                        </div>

                        {/* Content */}
                        <div className="px-6 py-4">
                            <p className="mb-4 text-sm text-gray-600">
                                Apakah Anda yakin ingin menghapus data karyawan
                                berikut?
                            </p>

                            <div className="p-4 border border-gray-200 bg-gray-50 rounded-xl">
                                <div className="flex items-center space-x-3">
                                    <div className="flex items-center justify-center w-12 h-12 bg-gradient-to-br from-[#439454] to-[#367a41] rounded-lg shadow-lg">
                                        <span className="text-lg font-bold text-white">
                                            {getInitials(employee.nama_lengkap)}
                                        </span>
                                    </div>
                                    <div>
                                        <p className="font-semibold text-gray-900">
                                            {employee.nama_lengkap}
                                        </p>
                                        <p className="text-sm text-gray-600">
                                            NIP: {employee.nip || "-"}
                                        </p>
                                        <p className="text-sm text-gray-600">
                                            {unitOrganisasiOriginal || "-"}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div className="p-3 mt-4 border border-red-200 rounded-lg bg-red-50">
                                <p className="text-sm font-medium text-red-800">
                                    Perhatian: Data yang sudah dihapus tidak
                                    dapat dikembalikan!
                                </p>
                            </div>
                        </div>

                        {/* Footer */}
                        <div className="flex justify-end px-6 py-4 space-x-3 border-t border-gray-200 bg-gray-50">
                            <button
                                onClick={() => setShowDeleteConfirm(false)}
                                disabled={deleteLoading}
                                className="px-4 py-2 text-sm font-medium text-gray-700 transition-all duration-200 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:border-gray-400 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                Batal
                            </button>
                            <button
                                onClick={handleDeleteEmployee}
                                disabled={deleteLoading}
                                className="flex items-center px-4 py-2 space-x-2 text-sm font-medium text-white transition-all duration-200 rounded-lg shadow-lg bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 disabled:opacity-50 disabled:cursor-not-allowed hover:shadow-xl"
                            >
                                {deleteLoading ? (
                                    <>
                                        <Loader2 className="w-4 h-4 animate-spin" />
                                        <span>Menghapus...</span>
                                    </>
                                ) : (
                                    <>
                                        <Trash2 className="w-4 h-4" />
                                        <span>Hapus Data</span>
                                    </>
                                )}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        );
    };

    return (
        <>
            <div
                className="fixed inset-0 z-50 overflow-y-auto animate-fadeIn"
                onClick={handleBackdropClick}
            >
                <div className="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                    <div
                        className="fixed inset-0 transition-all duration-300 bg-black/50 backdrop-blur-sm"
                        aria-hidden="true"
                    ></div>

                    <div className="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white shadow-2xl rounded-2xl sm:my-8 sm:align-middle sm:max-w-7xl sm:w-full animate-scaleIn">
                        {/* Header dengan informasi utama */}
                        <div className="relative px-8 py-6 bg-gradient-to-r from-[#439454] via-[#4a9f5e] to-[#367a41] text-white overflow-hidden">
                            <div className="absolute inset-0 bg-black/10"></div>
                            <div className="relative flex items-center justify-between">
                                <div className="flex items-center space-x-6">
                                    <div className="relative">
                                        <div className="flex items-center justify-center w-20 h-20 border shadow-2xl bg-white/20 backdrop-blur-sm rounded-2xl border-white/30">
                                            <span className="text-2xl font-bold text-white">
                                                {getInitials(
                                                    employee.nama_lengkap
                                                )}
                                            </span>
                                        </div>
                                        <div className="absolute w-6 h-6 bg-green-400 border-2 border-white rounded-full -bottom-1 -right-1"></div>
                                    </div>
                                    <div className="space-y-2">
                                        <h3 className="text-3xl font-bold text-white">
                                            {employee.nama_lengkap || "-"}
                                        </h3>
                                        <p className="text-xl font-medium text-white/90">
                                            NIP: {employee.nip || "-"}
                                        </p>

                                        {/* COMPLETELY UPDATED: Breakdown detail struktur organisasi dengan format kode lengkap */}
                                        <div className="flex flex-wrap gap-2 text-sm">
                                            {/* STRATEGY 1: Tampilkan unit organisasi dengan format kode jika tersedia */}
                                            {(() => {
                                                // Priority 1: Gunakan kode_organisasi untuk mapping unit
                                                if (employee.kode_organisasi) {
                                                    const unitDisplayMapping = {
                                                        EGM: "EGM",
                                                        GM: "GM",
                                                        MO: "(MO) Movement Operations",
                                                        ME: "(ME) Maintenance Equipment",
                                                        MF: "(MF) Movement Flight",
                                                        MS: "(MS) Movement Service",
                                                        MU: "(MU) Management Unit",
                                                        MK: "(MK) Management Keuangan",
                                                        MQ: "(MQ) Management Quality",
                                                        MB: "(MB) Management Business",
                                                    };

                                                    const formattedUnit =
                                                        unitDisplayMapping[
                                                            employee
                                                                .kode_organisasi
                                                        ];
                                                    if (formattedUnit) {
                                                        return (
                                                            <span className="inline-block px-3 py-1 font-semibold text-green-100 rounded-full bg-white/20">
                                                                {formattedUnit}
                                                            </span>
                                                        );
                                                    }
                                                }

                                                // Priority 2: Fallback menggunakan unit_organisasi + unit dengan mapping kode
                                                if (employee.unit_organisasi) {
                                                    const unitCodeMapping = {
                                                        Airside: {
                                                            "Movement Operations":
                                                                "MO",
                                                            "Maintenance Equipment":
                                                                "ME",
                                                        },
                                                        Landside: {
                                                            "Movement Flight":
                                                                "MF",
                                                            "Movement Service":
                                                                "MS",
                                                        },
                                                        "Back Office": {
                                                            "Management Keuangan":
                                                                "MK",
                                                            "Management Unit":
                                                                "MU",
                                                        },
                                                        SSQC: {
                                                            "Management Quality":
                                                                "MQ",
                                                        },
                                                        Ancillary: {
                                                            "Management Business":
                                                                "MB",
                                                        },
                                                    };

                                                    // Jika ada unit.name dan bisa dimapping ke kode
                                                    if (
                                                        employee.unit &&
                                                        employee.unit.name &&
                                                        unitCodeMapping[
                                                            employee
                                                                .unit_organisasi
                                                        ] &&
                                                        unitCodeMapping[
                                                            employee
                                                                .unit_organisasi
                                                        ][employee.unit.name]
                                                    ) {
                                                        const code =
                                                            unitCodeMapping[
                                                                employee
                                                                    .unit_organisasi
                                                            ][
                                                                employee.unit
                                                                    .name
                                                            ];
                                                        return (
                                                            <span className="inline-block px-3 py-1 font-semibold text-green-100 rounded-full bg-white/20">
                                                                ({code}){" "}
                                                                {
                                                                    employee
                                                                        .unit
                                                                        .name
                                                                }
                                                            </span>
                                                        );
                                                    }

                                                    // Fallback: tampilkan unit_organisasi saja jika tidak bisa dimapping
                                                    return (
                                                        <span className="inline-block px-3 py-1 font-semibold text-green-100 rounded-full bg-white/20">
                                                            {
                                                                employee.unit_organisasi
                                                            }
                                                        </span>
                                                    );
                                                }

                                                return null;
                                            })()}

                                            {/* STRATEGY 2: Tampilkan unit jika berbeda dari unit organisasi dan belum ter-cover di atas */}
                                            {(() => {
                                                // Hanya tampilkan unit terpisah jika tidak sama dengan unit organisasi
                                                if (
                                                    employee.unit &&
                                                    employee.unit.name &&
                                                    employee.unit.name !==
                                                        employee.unit_organisasi &&
                                                    !employee.kode_organisasi
                                                ) {
                                                    return (
                                                        <span className="inline-block px-3 py-1 font-semibold text-blue-100 rounded-full bg-white/20">
                                                            Unit:{" "}
                                                            {employee.unit.name}
                                                        </span>
                                                    );
                                                }
                                                return null;
                                            })()}

                                            {/* STRATEGY 3: Selalu tampilkan sub unit jika tersedia */}
                                            {employee.sub_unit &&
                                                employee.sub_unit.name && (
                                                    <span className="inline-block px-3 py-1 font-semibold text-purple-100 rounded-full bg-white/20">
                                                        Sub Unit:{" "}
                                                        {employee.sub_unit.name}
                                                    </span>
                                                )}

                                            {/* STRATEGY 4: Fallback untuk nama_organisasi jika tersedia dan berbeda */}
                                            {employee.nama_organisasi &&
                                                employee.nama_organisasi !==
                                                    employee.unit_organisasi &&
                                                (!employee.unit ||
                                                    employee.nama_organisasi !==
                                                        employee.unit.name) && (
                                                    <span className="inline-block px-3 py-1 font-semibold text-yellow-100 rounded-full bg-white/20">
                                                        {
                                                            employee.nama_organisasi
                                                        }
                                                    </span>
                                                )}
                                        </div>
                                    </div>
                                </div>
                                <button
                                    onClick={onClose}
                                    className="p-3 transition-all duration-300 group rounded-xl hover:bg-white/20 hover:rotate-90 backdrop-blur-sm"
                                >
                                    <X className="w-6 h-6 text-white transition-transform duration-300 group-hover:scale-110" />
                                </button>
                            </div>
                        </div>

                        {/* Content */}
                        <div className="bg-gradient-to-br from-gray-50 via-white to-gray-50 px-8 py-8 max-h-[80vh] overflow-y-auto">
                            <div className="space-y-8">
                                {/* 1. Data Pekerjaan - PALING ATAS dengan format kode yang konsisten */}
                                <DetailCard
                                    title="Data Pekerjaan"
                                    icon={Building2}
                                >
                                    <div className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                                        {/* UPDATED: Urutan sesuai permintaan dengan format kode */}
                                        <FieldRow
                                            label="Unit Organisasi"
                                            value={unitOrganisasiFormatted}
                                        />
                                        <FieldRow
                                            label="Unit"
                                            value={unitNameFormatted}
                                        />
                                        <FieldRow
                                            label="Sub Unit"
                                            value={subUnitNameFormatted}
                                        />
                                        <FieldRow
                                            label="Nama Jabatan"
                                            value={employee.nama_jabatan}
                                        />
                                        <FieldRow
                                            label="Status Pegawai"
                                            value={employee.status_pegawai}
                                        />
                                        <FieldRow
                                            label="Kelompok Jabatan"
                                            value={employee.kelompok_jabatan}
                                        />
                                        <FieldRow
                                            label="Provider"
                                            value={employee.provider}
                                        />
                                        <FieldRow
                                            label="TMT Mulai Kerja"
                                            value={formatDate(
                                                employee.tmt_mulai_kerja
                                            )}
                                        />
                                        <FieldRow
                                            label="TMT Berakhir Kerja"
                                            value={formatDate(
                                                employee.tmt_berakhir_kerja
                                            )}
                                        />
                                        <FieldRow
                                            label="TMT Mulai Jabatan"
                                            value={formatDate(
                                                employee.tmt_mulai_jabatan
                                            )}
                                        />
                                        <FieldRow
                                            label="TMT Akhir Jabatan"
                                            value={formatDate(
                                                employee.tmt_akhir_jabatan ||
                                                    employee.tmt_berakhir_jabatan
                                            )}
                                        />
                                        <FieldRow
                                            label="Status Kerja"
                                            value={employee.status_kerja}
                                        />
                                        <FieldRow
                                            label="Unit Kerja Sesuai Kontrak"
                                            value={employee.unit_kerja_kontrak}
                                        />
                                        <FieldRow
                                            label="Grade"
                                            value={employee.grade}
                                        />
                                        <FieldRow
                                            label="Lokasi Kerja"
                                            value={employee.lokasi_kerja}
                                        />
                                        <FieldRow
                                            label="Cabang"
                                            value={employee.cabang}
                                        />
                                        <FieldRow
                                            label="Masa Kerja"
                                            value={masaKerjaResult}
                                        />
                                        <FieldRow
                                            label="TMT Pensiun"
                                            value={formatDate(
                                                employee.tmt_pensiun
                                            )}
                                        />
                                    </div>
                                </DetailCard>

                                {/* 2. Data Pribadi */}
                                <DetailCard title="Data Pribadi" icon={User}>
                                    <div className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                                        <FieldRow
                                            label="NIK"
                                            value={employee.nik}
                                        />
                                        <FieldRow
                                            label="NIP"
                                            value={employee.nip}
                                        />
                                        <FieldRow
                                            label="Nama Lengkap"
                                            value={employee.nama_lengkap}
                                        />
                                        <FieldRow
                                            label="Jenis Kelamin"
                                            value={formatGender(
                                                employee.jenis_kelamin
                                            )}
                                        />
                                        <FieldRow
                                            label="Tempat Lahir"
                                            value={employee.tempat_lahir}
                                        />
                                        <FieldRow
                                            label="Tanggal Lahir"
                                            value={formatDate(
                                                employee.tanggal_lahir
                                            )}
                                        />
                                        <FieldRow
                                            label="Usia"
                                            value={
                                                employee.usia
                                                    ? `${employee.usia} tahun`
                                                    : "-"
                                            }
                                        />
                                        <FieldRow
                                            label="Kota Domisili"
                                            value={employee.kota_domisili}
                                        />
                                        <FieldRow
                                            label="Alamat Lengkap"
                                            value={
                                                employee.alamat_lengkap ||
                                                employee.alamat
                                            }
                                            colSpan={3}
                                        />
                                        <FieldRow
                                            label="Handphone"
                                            value={
                                                employee.handphone ||
                                                employee.no_telepon
                                            }
                                        />
                                        <FieldRow
                                            label="Email"
                                            value={employee.email}
                                        />
                                        <FieldRow
                                            label="No. BPJS Kesehatan"
                                            value={employee.no_bpjs_kesehatan}
                                        />
                                        <FieldRow
                                            label="No. BPJS Ketenagakerjaan"
                                            value={
                                                employee.no_bpjs_ketenagakerjaan
                                            }
                                        />
                                        <FieldRow
                                            label="Tinggi Badan"
                                            value={
                                                employee.height
                                                    ? `${employee.height} cm`
                                                    : "-"
                                            }
                                        />
                                        <FieldRow
                                            label="Berat Badan"
                                            value={
                                                employee.weight
                                                    ? `${employee.weight} kg`
                                                    : "-"
                                            }
                                        />
                                    </div>
                                </DetailCard>

                                {/* 3. Data Pendidikan */}
                                <DetailCard
                                    title="Data Pendidikan"
                                    icon={GraduationCap}
                                >
                                    <div className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                                        <FieldRow
                                            label="Pendidikan Terakhir"
                                            value={
                                                employee.pendidikan_terakhir ||
                                                employee.tingkat_pendidikan
                                            }
                                        />
                                        <FieldRow
                                            label="Instansi Pendidikan"
                                            value={employee.instansi_pendidikan}
                                        />
                                        <FieldRow
                                            label="Jurusan"
                                            value={employee.jurusan}
                                        />
                                        <FieldRow
                                            label="Tahun Lulus"
                                            value={employee.tahun_lulus}
                                        />
                                    </div>
                                </DetailCard>

                                {/* 4. Data Tambahan */}
                                <DetailCard
                                    title="Data Tambahan"
                                    icon={FileText}
                                >
                                    <div className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                                        <FieldRow
                                            label="Seragam"
                                            value={employee.seragam}
                                        />
                                        <FieldRow
                                            label="Jenis Sepatu"
                                            value={employee.jenis_sepatu}
                                        />
                                        <FieldRow
                                            label="Ukuran Sepatu"
                                            value={employee.ukuran_sepatu}
                                        />
                                    </div>
                                </DetailCard>
                            </div>
                        </div>

                        {/* Footer dengan tombol aksi */}
                        <div className="flex justify-between px-8 py-6 border-t border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100">
                            {/* Left side - Delete button */}
                            <button
                                onClick={() => setShowDeleteConfirm(true)}
                                className="group px-4 py-3 bg-gradient-to-r from-red-500 to-red-600 border-2 border-transparent rounded-xl text-sm font-semibold text-white hover:from-red-600 hover:to-red-700 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center space-x-2"
                            >
                                <Trash2 className="w-4 h-4" />
                                <span>Hapus Data</span>
                            </button>

                            {/* Right side - Close and Edit buttons */}
                            <div className="flex space-x-4">
                                <button
                                    onClick={onClose}
                                    className="group px-6 py-3 bg-white border-2 border-gray-300 rounded-xl text-sm font-semibold text-gray-700 hover:bg-gray-50 hover:border-[#439454] hover:text-[#439454] transition-all duration-300 shadow-sm hover:shadow-md transform hover:-translate-y-0.5"
                                >
                                    Tutup
                                </button>
                                {employee.id && (
                                    <a
                                        href={`/employees/${employee.id}/edit`}
                                        className="group px-6 py-3 bg-gradient-to-r from-[#439454] to-[#367a41] border-2 border-transparent rounded-xl text-sm font-semibold text-white hover:from-[#367a41] hover:to-[#2d6435] transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
                                    >
                                        Edit Karyawan
                                    </a>
                                )}
                            </div>
                        </div>
                    </div>
                </div>

                <style jsx>{`
                    @keyframes fadeIn {
                        from {
                            opacity: 0;
                        }
                        to {
                            opacity: 1;
                        }
                    }

                    @keyframes scaleIn {
                        from {
                            opacity: 0;
                            transform: scale(0.9) translateY(20px);
                        }
                        to {
                            opacity: 1;
                            transform: scale(1) translateY(0);
                        }
                    }

                    .animate-fadeIn {
                        animation: fadeIn 0.3s ease-out;
                    }

                    .animate-scaleIn {
                        animation: scaleIn 0.4s ease-out;
                    }
                `}</style>
            </div>

            {/* Delete Confirmation Modal */}
            <DeleteConfirmationModal />
        </>
    );
};

export default EmployeeDetailModal;
