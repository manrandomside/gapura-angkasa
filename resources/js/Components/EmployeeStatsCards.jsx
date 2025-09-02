// resources/js/Components/EmployeeStatsCards.jsx
import React, { useState, useEffect, useMemo, useCallback } from "react";
import {
    Users,
    UserCheck,
    CalendarClock,
    Star,
    TrendingUp,
    Clock,
    AlertCircle,
    CheckCircle2,
    RefreshCw,
} from "lucide-react";
import axios from "axios";

const EmployeeStatsCards = ({
    statistics: initialStatistics,
    employees,
    timezone = "Asia/Makassar",
    onStatsUpdate,
    enableAutoRefresh = false,
    refreshInterval = 30000, // 30 seconds default
}) => {
    const [currentTime, setCurrentTime] = useState(new Date());
    const [statistics, setStatistics] = useState(initialStatistics || {});
    const [isRefreshing, setIsRefreshing] = useState(false);
    const [refreshKey, setRefreshKey] = useState(0);

    // Update current time every minute for WITA timezone
    useEffect(() => {
        const timer = setInterval(() => {
            setCurrentTime(new Date());
        }, 60000); // Update every minute

        return () => clearInterval(timer);
    }, []);

    // Auto refresh functionality
    useEffect(() => {
        if (!enableAutoRefresh) return;

        const refreshTimer = setInterval(() => {
            refreshStatistics();
        }, refreshInterval);

        return () => clearInterval(refreshTimer);
    }, [enableAutoRefresh, refreshInterval]);

    // Update statistics when initialStatistics prop changes
    useEffect(() => {
        if (initialStatistics) {
            setStatistics(initialStatistics);
        }
    }, [initialStatistics]);

    // Method to refresh statistics from backend
    const refreshStatistics = useCallback(async () => {
        if (isRefreshing) return;

        setIsRefreshing(true);

        try {
            console.log("EmployeeStatsCards: Refreshing statistics...");

            const response = await axios.get("/api/employees/statistics", {
                headers: {
                    "Content-Type": "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                },
            });

            if (response.data) {
                console.log(
                    "EmployeeStatsCards: Statistics refreshed",
                    response.data
                );
                setStatistics(response.data);
                setRefreshKey((prev) => prev + 1);

                // Notify parent component of update
                if (onStatsUpdate) {
                    onStatsUpdate(response.data);
                }
            }
        } catch (error) {
            console.error(
                "EmployeeStatsCards: Error refreshing statistics",
                error
            );

            // Fallback: try to get statistics from current employees data
            if (employees?.data) {
                const fallbackStats = calculateFallbackStats(employees.data);
                setStatistics(fallbackStats);

                if (onStatsUpdate) {
                    onStatsUpdate(fallbackStats);
                }
            }
        } finally {
            setIsRefreshing(false);
        }
    }, [isRefreshing, onStatsUpdate, employees]);

    // Calculate fallback statistics from employees data
    const calculateFallbackStats = useCallback((employeesData) => {
        if (!employeesData || !Array.isArray(employeesData)) return {};

        const total = employeesData.length;
        const pegawaiTetap = employeesData.filter(
            (emp) => emp.status_pegawai === "PEGAWAI TETAP"
        ).length;
        const pkwt = employeesData.filter(
            (emp) => emp.status_pegawai === "PKWT"
        ).length;

        // TAD calculation with different variations
        const tadPaketSDM = employeesData.filter(
            (emp) => emp.status_pegawai === "TAD PAKET SDM"
        ).length;
        const tadPaketPekerjaan = employeesData.filter(
            (emp) => emp.status_pegawai === "TAD PAKET PEKERJAAN"
        ).length;
        const tadLegacy = employeesData.filter(
            (emp) => emp.status_pegawai === "TAD"
        ).length;
        const tadTotal = tadPaketSDM + tadPaketPekerjaan + tadLegacy;

        // Unique units calculation
        const uniqueUnits = [
            ...new Set(
                employeesData.map((emp) => emp.unit_organisasi).filter(Boolean)
            ),
        ].length;

        return {
            total,
            pegawaiTetap,
            pkwt,
            tad: tadTotal,
            tad_total: tadTotal,
            tad_paket_sdm: tadPaketSDM,
            tad_paket_pekerjaan: tadPaketPekerjaan,
            uniqueUnits,
        };
    }, []);

    // Method to handle employee deletion (external call)
    const handleEmployeeDeleted = useCallback(
        async (deletedEmployee) => {
            console.log(
                "EmployeeStatsCards: Employee deleted, updating statistics",
                deletedEmployee
            );

            // Immediately update local statistics
            setStatistics((prev) => ({
                ...prev,
                total: Math.max(0, (prev.total || 0) - 1),
                // Update specific status counts based on deleted employee
                pegawaiTetap:
                    deletedEmployee?.status_pegawai === "PEGAWAI TETAP"
                        ? Math.max(0, (prev.pegawaiTetap || 0) - 1)
                        : prev.pegawaiTetap,
                tad: deletedEmployee?.status_pegawai?.includes("TAD")
                    ? Math.max(0, (prev.tad || 0) - 1)
                    : prev.tad,
                pkwt:
                    deletedEmployee?.status_pegawai === "PKWT"
                        ? Math.max(0, (prev.pkwt || 0) - 1)
                        : prev.pkwt,
            }));

            // Force refresh from server after a short delay
            setTimeout(() => {
                refreshStatistics();
            }, 500);
        },
        [refreshStatistics]
    );

    // Expose refresh method to parent component
    useEffect(() => {
        if (onStatsUpdate && typeof onStatsUpdate === "function") {
            // Add refresh method to the callback
            onStatsUpdate.refresh = refreshStatistics;
            onStatsUpdate.handleEmployeeDeleted = handleEmployeeDeleted;
        }
    }, [refreshStatistics, handleEmployeeDeleted, onStatsUpdate]);

    // Enhanced stats calculation with timezone support
    const enhancedStats = useMemo(() => {
        const data = employees?.data || [];

        // Get current date in WITA timezone
        const witaDate = new Date(
            currentTime.toLocaleString("en-US", { timeZone: timezone })
        );
        const today = new Date(
            witaDate.getFullYear(),
            witaDate.getMonth(),
            witaDate.getDate()
        );

        // Get yesterday for comparison
        const yesterday = new Date(today);
        yesterday.setDate(yesterday.getDate() - 1);

        // Get this week start (Monday)
        const thisWeekStart = new Date(today);
        thisWeekStart.setDate(today.getDate() - (today.getDay() || 7) + 1);

        // Calculate new employees for different periods
        const newToday = data.filter((employee) => {
            const createdDate = new Date(employee.created_at);
            // Convert to WITA timezone for comparison
            const empWitaDate = new Date(
                createdDate.toLocaleString("en-US", { timeZone: timezone })
            );
            const empDate = new Date(
                empWitaDate.getFullYear(),
                empWitaDate.getMonth(),
                empWitaDate.getDate()
            );
            return empDate.getTime() === today.getTime();
        });

        const newYesterday = data.filter((employee) => {
            const createdDate = new Date(employee.created_at);
            const empWitaDate = new Date(
                createdDate.toLocaleString("en-US", { timeZone: timezone })
            );
            const empDate = new Date(
                empWitaDate.getFullYear(),
                empWitaDate.getMonth(),
                empWitaDate.getDate()
            );
            return empDate.getTime() === yesterday.getTime();
        });

        const newThisWeek = data.filter((employee) => {
            const createdDate = new Date(employee.created_at);
            const empWitaDate = new Date(
                createdDate.toLocaleString("en-US", { timeZone: timezone })
            );
            const empDate = new Date(
                empWitaDate.getFullYear(),
                empWitaDate.getMonth(),
                empWitaDate.getDate()
            );
            return empDate >= thisWeekStart;
        });

        // Get time-based period info
        const getTimePeriodInfo = () => {
            const hour = parseInt(
                new Intl.DateTimeFormat("id-ID", {
                    timeZone: timezone,
                    hour: "2-digit",
                    hour12: false,
                }).format(witaDate)
            );

            if (hour >= 5 && hour < 12) {
                return {
                    period: "Pagi Ini",
                    bgGradient: "from-orange-400 to-orange-500",
                    textColor: "text-orange-800",
                };
            } else if (hour >= 12 && hour < 17) {
                return {
                    period: "Siang Ini",
                    bgGradient: "from-blue-400 to-blue-500",
                    textColor: "text-blue-800",
                };
            } else if (hour >= 17 && hour < 21) {
                return {
                    period: "Sore Ini",
                    bgGradient: "from-purple-400 to-purple-500",
                    textColor: "text-purple-800",
                };
            } else {
                return {
                    period: "Malam Ini",
                    bgGradient: "from-indigo-400 to-indigo-500",
                    textColor: "text-indigo-800",
                };
            }
        };

        return {
            total: statistics.total || 0,
            pegawaiTetap: statistics.pegawaiTetap || 0,
            tad: statistics.tad || 0,
            uniqueUnits: statistics.uniqueUnits || 0,
            newToday: newToday.length,
            newYesterday: newYesterday.length,
            newThisWeek: newThisWeek.length,
            newTodayEmployees: newToday,
            timePeriod: getTimePeriodInfo(),
            witaTime: witaDate.toLocaleString("id-ID", {
                timeZone: timezone,
                weekday: "long",
                year: "numeric",
                month: "long",
                day: "numeric",
                hour: "2-digit",
                minute: "2-digit",
            }),
        };
    }, [statistics, employees, currentTime, timezone, refreshKey]);

    const statsCards = [
        {
            title: "Total Karyawan",
            value: enhancedStats.total,
            subtitle: "Seluruh karyawan",
            icon: Users,
            gradient: "from-blue-500 to-blue-600",
            bgLight: "bg-blue-50",
            textColor: "text-blue-800",
            hoverColor: "hover:text-blue-700",
        },
        {
            title: "Pegawai Tetap",
            value: enhancedStats.pegawaiTetap,
            subtitle: "Status tetap",
            icon: UserCheck,
            gradient: "from-[#439454] to-green-600",
            bgLight: "bg-green-50",
            textColor: "text-green-800",
            hoverColor: "hover:text-green-700",
        },
        {
            title: "TAD",
            value: enhancedStats.tad,
            subtitle: "Tenaga Alih Daya",
            icon: CalendarClock,
            gradient: "from-yellow-500 to-yellow-600",
            bgLight: "bg-yellow-50",
            textColor: "text-yellow-800",
            hoverColor: "hover:text-yellow-700",
        },
        {
            title:
                enhancedStats.newToday > 0
                    ? enhancedStats.timePeriod.period
                    : "Unit Organisasi",
            value:
                enhancedStats.newToday > 0
                    ? enhancedStats.newToday
                    : enhancedStats.uniqueUnits,
            subtitle:
                enhancedStats.newToday > 0 ? "Karyawan baru" : "Total unit",
            icon: enhancedStats.newToday > 0 ? Star : Users,
            gradient:
                enhancedStats.newToday > 0
                    ? enhancedStats.timePeriod.bgGradient
                    : "from-purple-500 to-purple-600",
            bgLight:
                enhancedStats.newToday > 0 ? "bg-orange-50" : "bg-purple-50",
            textColor:
                enhancedStats.newToday > 0
                    ? enhancedStats.timePeriod.textColor
                    : "text-purple-800",
            hoverColor:
                enhancedStats.newToday > 0
                    ? "hover:text-orange-700"
                    : "hover:text-purple-700",
            isNew: enhancedStats.newToday > 0,
            newEmployees: enhancedStats.newTodayEmployees,
        },
    ];

    return (
        <div className="relative">
            {/* Refresh button for manual updates */}
            {onStatsUpdate && (
                <div className="absolute right-0 z-10 -top-12">
                    <button
                        onClick={refreshStatistics}
                        disabled={isRefreshing}
                        className={`p-2 text-gray-500 hover:text-[#439454] hover:bg-green-50 rounded-lg transition-all duration-200 ${
                            isRefreshing ? "animate-spin" : ""
                        }`}
                        title="Refresh statistik"
                    >
                        <RefreshCw
                            className={`w-5 h-5 ${
                                isRefreshing ? "animate-spin" : ""
                            }`}
                        />
                    </button>
                </div>
            )}

            <div className="grid grid-cols-1 gap-6 mb-8 md:grid-cols-2 lg:grid-cols-4">
                {statsCards.map((card, index) => (
                    <div
                        key={`${index}-${refreshKey}`}
                        className={`stat-card relative overflow-hidden transition-all duration-700 transform bg-white border border-gray-100 shadow-xl group rounded-3xl hover:shadow-2xl hover:-translate-y-4 hover:scale-105 ${
                            card.isNew
                                ? "ring-2 ring-orange-300 ring-opacity-50"
                                : ""
                        } ${isRefreshing ? "opacity-75" : "opacity-100"}`}
                    >
                        {/* Background Animation */}
                        <div className="absolute inset-0 transition-all duration-700 opacity-0 bg-gradient-to-br from-gray-50/50 to-gray-100/80 group-hover:opacity-100"></div>

                        {/* Refresh indicator */}
                        {isRefreshing && (
                            <div className="absolute z-10 top-2 left-2">
                                <div className="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></div>
                            </div>
                        )}

                        {/* New Badge Animation for new employees */}
                        {card.isNew && (
                            <div className="absolute z-10 -top-2 -right-2">
                                <div className="px-3 py-1 text-xs font-bold text-white rounded-full shadow-lg bg-gradient-to-r from-orange-400 to-orange-500 animate-pulse">
                                    NEW
                                </div>
                            </div>
                        )}

                        <div className="relative p-8">
                            <div className="flex items-center justify-between mb-8">
                                <div
                                    className={`flex items-center justify-center transition-all duration-700 shadow-2xl w-18 h-18 bg-gradient-to-br ${card.gradient} rounded-3xl group-hover:scale-125 group-hover:rotate-6`}
                                >
                                    <card.icon className="text-white transition-all duration-700 w-9 h-9 group-hover:scale-110" />
                                </div>

                                {/* Growth indicator for new employees */}
                                {card.isNew &&
                                    enhancedStats.newYesterday > 0 && (
                                        <div className="flex items-center space-x-1 text-sm">
                                            {enhancedStats.newToday >
                                            enhancedStats.newYesterday ? (
                                                <>
                                                    <TrendingUp className="w-4 h-4 text-green-500" />
                                                    <span className="font-medium text-green-600">
                                                        +
                                                        {enhancedStats.newToday -
                                                            enhancedStats.newYesterday}
                                                    </span>
                                                </>
                                            ) : (
                                                <CheckCircle2 className="w-4 h-4 text-blue-500" />
                                            )}
                                        </div>
                                    )}
                            </div>

                            <div className="space-y-3">
                                <p
                                    className={`text-4xl font-bold transition-transform duration-300 group-hover:scale-105 ${card.textColor}`}
                                >
                                    {card.value.toLocaleString("id-ID")}
                                </p>

                                <p
                                    className={`text-lg font-semibold text-gray-600 transition-all duration-700 ${card.hoverColor}`}
                                >
                                    {card.title}
                                </p>

                                <div
                                    className={`inline-block px-4 py-2 text-sm font-medium rounded-full transition-all duration-700 ${card.bgLight} ${card.textColor} group-hover:bg-white group-hover:scale-105`}
                                >
                                    {card.subtitle}
                                </div>

                                {/* Additional info for new employees */}
                                {card.isNew &&
                                    card.newEmployees &&
                                    card.newEmployees.length > 0 && (
                                        <div className="pt-3 mt-4 border-t border-gray-100">
                                            <div className="flex items-center mb-2 space-x-2">
                                                <Clock className="w-4 h-4 text-gray-500" />
                                                <span className="text-xs text-gray-500">
                                                    {enhancedStats.witaTime}
                                                </span>
                                            </div>
                                            <div className="text-xs text-gray-600">
                                                <p className="font-medium">
                                                    Karyawan terbaru:
                                                </p>
                                                <p className="mt-1 truncate">
                                                    {
                                                        card.newEmployees[0]
                                                            ?.nama_lengkap
                                                    }
                                                    {card.newEmployees.length >
                                                        1 && (
                                                        <span className="text-gray-500">
                                                            {" "}
                                                            dan{" "}
                                                            {card.newEmployees
                                                                .length -
                                                                1}{" "}
                                                            lainnya
                                                        </span>
                                                    )}
                                                </p>
                                            </div>
                                        </div>
                                    )}

                                {/* Week summary for new employees card */}
                                {card.isNew &&
                                    enhancedStats.newThisWeek >
                                        enhancedStats.newToday && (
                                        <div className="pt-2 text-xs text-gray-500 border-t border-gray-100">
                                            <div className="flex items-center space-x-1">
                                                <AlertCircle className="w-3 h-3" />
                                                <span>
                                                    {enhancedStats.newThisWeek}{" "}
                                                    karyawan minggu ini
                                                </span>
                                            </div>
                                        </div>
                                    )}
                            </div>
                        </div>
                    </div>
                ))}
            </div>
        </div>
    );
};

export default EmployeeStatsCards;
