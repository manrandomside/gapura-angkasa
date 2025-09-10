import React, { useState, useEffect, useCallback } from "react";
import { Head } from "@inertiajs/react";
import DashboardLayout from "../../Layouts/DashboardLayout";

export default function Index({ statistics = {} }) {
    const [stats, setStats] = useState({
        total_employees: 0,
        active_employees: 0,
        pegawai_tetap: 0,
        pkwt: 0,
        tad_total: 0,
        tad_paket_sdm: 0,
        tad_paket_pekerjaan: 0,
        ...statistics,
    });
    const [chartData, setChartData] = useState({
        gender: [],
        status: [],
        unit: [],
        provider: [],
        age: [],
        jabatan: [],
    });
    const [loading, setLoading] = useState(true);
    const [lastUpdate, setLastUpdate] = useState(Date.now());
    const [isRefreshing, setIsRefreshing] = useState(false);

    const BASE_COLOR = "#439454";

    // Enhanced color palette with highly distinctive colors
    const CHART_COLORS = {
        primary: "#439454", // Required green color
        crimson: "#DC2626", // Bright red
        navy: "#1E3A8A", // Dark blue
        orange: "#EA580C", // Bright orange
        purple: "#7C3AED", // Bright purple
        teal: "#0F766E", // Dark teal
        amber: "#D97706", // Amber orange
        pink: "#EC4899", // Bright pink
        indigo: "#4338CA", // Indigo blue
        emerald: "#059669", // Different green shade
        slate: "#475569", // Dark slate
        rose: "#F43F5E", // Rose pink
        cyan: "#0891B2", // Cyan blue
        lime: "#65A30D", // Lime green
        violet: "#8B5CF6", // Violet purple
        yellow: "#EAB308", // Golden yellow
    };

    const fetchStatistics = useCallback(async () => {
        try {
            console.log("Fetching statistics...");
            const response = await fetch("/api/dashboard/statistics");
            if (response.ok) {
                const data = await response.json();
                console.log("Statistics received:", data);
                setStats((prevStats) => ({
                    ...prevStats,
                    ...data,
                }));
            } else {
                console.error("Statistics fetch failed:", response.status);
            }
        } catch (error) {
            console.error("Error fetching statistics:", error);
        }
    }, []);

    const fetchChartData = useCallback(async () => {
        try {
            console.log("Fetching chart data...");
            const response = await fetch("/api/dashboard/charts");
            if (response.ok) {
                const data = await response.json();
                console.log("Chart data received:", data);

                // Debug specific chart data
                console.log("Age chart data:", data.age);
                console.log("Status chart data:", data.status);
                console.log("Jabatan chart data:", data.jabatan);

                setChartData(data);
            } else {
                console.error(
                    "Chart data fetch failed:",
                    response.status,
                    await response.text()
                );
            }
        } catch (error) {
            console.error("Error fetching chart data:", error);
        }
    }, []);

    const fetchAllData = useCallback(async () => {
        console.log("Starting data fetch...");
        setLoading(true);
        try {
            await Promise.all([fetchStatistics(), fetchChartData()]);
            setLastUpdate(Date.now());
            console.log("All data fetched successfully");
        } catch (error) {
            console.error("Error fetching all data:", error);
        } finally {
            setLoading(false);
        }
    }, [fetchStatistics, fetchChartData]);

    // Manual refresh function for real-time updates
    const manualRefresh = useCallback(async () => {
        if (isRefreshing) {
            console.log("Refresh already in progress, skipping...");
            return;
        }

        console.log("Manual refresh triggered");
        setIsRefreshing(true);
        try {
            await Promise.all([fetchStatistics(), fetchChartData()]);
            setLastUpdate(Date.now());
            console.log("Manual refresh completed");
        } catch (error) {
            console.error("Error during manual refresh:", error);
        } finally {
            setIsRefreshing(false);
        }
    }, [fetchStatistics, fetchChartData, isRefreshing]);

    // Expose refresh function globally for trigger from other components
    useEffect(() => {
        window.dashboardRefresh = manualRefresh;
        return () => {
            if (window.dashboardRefresh) {
                delete window.dashboardRefresh;
            }
        };
    }, [manualRefresh]);

    // Listen for window focus to refresh data
    useEffect(() => {
        const handleFocus = () => {
            // Optional: refresh when user returns to tab
        };

        const handleVisibilityChange = () => {
            if (!document.hidden) {
                // Optional: refresh when tab becomes visible
            }
        };

        window.addEventListener("focus", handleFocus);
        document.addEventListener("visibilitychange", handleVisibilityChange);

        return () => {
            window.removeEventListener("focus", handleFocus);
            document.removeEventListener(
                "visibilitychange",
                handleVisibilityChange
            );
        };
    }, [manualRefresh]);

    // Listen for custom events for real-time updates
    useEffect(() => {
        const handleEmployeeDataChange = () => {
            console.log(
                "Employee data change event received, refreshing dashboard..."
            );
            manualRefresh();
        };

        window.addEventListener("employee-added", handleEmployeeDataChange);
        window.addEventListener("employee-updated", handleEmployeeDataChange);
        window.addEventListener("employee-deleted", handleEmployeeDataChange);
        window.addEventListener("dashboard-refresh", handleEmployeeDataChange);

        return () => {
            window.removeEventListener(
                "employee-added",
                handleEmployeeDataChange
            );
            window.removeEventListener(
                "employee-updated",
                handleEmployeeDataChange
            );
            window.removeEventListener(
                "employee-deleted",
                handleEmployeeDataChange
            );
            window.removeEventListener(
                "dashboard-refresh",
                handleEmployeeDataChange
            );
        };
    }, [manualRefresh]);

    // Initial data load only
    useEffect(() => {
        fetchAllData();
    }, []);

    /**
     * NEW: Detail Container Component for displaying breakdown information
     */
    const DetailContainer = ({ data, chartType, title }) => {
        if (!data || data.length === 0) {
            return (
                <div className="p-6 mt-6 border border-gray-200 shadow-sm bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl">
                    <div className="text-center">
                        <div className="inline-flex items-center justify-center w-12 h-12 mb-3 bg-gray-200 rounded-full">
                            <svg
                                className="w-6 h-6 text-gray-400"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    strokeWidth={2}
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                />
                            </svg>
                        </div>
                        <p className="text-sm font-medium text-gray-500">
                            Tidak ada data detail tersedia
                        </p>
                    </div>
                </div>
            );
        }

        const total = data.reduce((sum, item) => sum + (item.value || 0), 0);
        const colorArray = Object.values(CHART_COLORS);

        const renderDetailContent = () => {
            switch (chartType) {
                case "gender":
                    return (
                        <div className="space-y-4">
                            <h4 className="pb-2 text-lg font-bold text-gray-900 border-b border-gray-200">
                                Detail Jenis Kelamin
                            </h4>
                            <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                                {data.map((item, index) => {
                                    const percentage =
                                        total > 0
                                            ? (
                                                  ((item.value || 0) / total) *
                                                  100
                                              ).toFixed(1)
                                            : 0;
                                    const color =
                                        index === 0
                                            ? CHART_COLORS.primary
                                            : colorArray[
                                                  (index + 1) %
                                                      colorArray.length
                                              ];
                                    return (
                                        <div
                                            key={index}
                                            className="flex items-center p-4 bg-white border border-gray-100 rounded-lg shadow-sm"
                                        >
                                            <div
                                                className="w-4 h-4 mr-3 border-2 border-white rounded-full shadow-sm"
                                                style={{
                                                    backgroundColor: color,
                                                }}
                                            ></div>
                                            <div className="flex-1">
                                                <p className="text-sm font-semibold text-gray-900">
                                                    {item.name}
                                                </p>
                                                <p className="text-lg font-bold text-gray-800">
                                                    {item.value || 0}{" "}
                                                    <span className="text-sm font-normal text-gray-500">
                                                        ({percentage}%)
                                                    </span>
                                                </p>
                                            </div>
                                        </div>
                                    );
                                })}
                            </div>
                        </div>
                    );

                case "status":
                    return (
                        <div className="space-y-4">
                            <h4 className="pb-2 text-lg font-bold text-gray-900 border-b border-gray-200">
                                Detail Status Pegawai
                            </h4>
                            <div className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                                {data.map((item, index) => {
                                    const color =
                                        index === 0
                                            ? CHART_COLORS.primary
                                            : colorArray[
                                                  (index + 1) %
                                                      colorArray.length
                                              ];
                                    return (
                                        <div
                                            key={index}
                                            className="flex items-center p-4 bg-white border border-gray-100 rounded-lg shadow-sm"
                                        >
                                            <div
                                                className="w-4 h-4 mr-3 border-2 border-white rounded shadow-sm"
                                                style={{
                                                    backgroundColor: color,
                                                }}
                                            ></div>
                                            <div className="flex-1">
                                                <p className="text-sm font-semibold text-gray-900 uppercase">
                                                    {item.name}
                                                </p>
                                                <p
                                                    className="text-xl font-bold"
                                                    style={{ color: color }}
                                                >
                                                    {item.value || 0}
                                                </p>
                                            </div>
                                        </div>
                                    );
                                })}
                            </div>
                        </div>
                    );

                case "unit":
                    return (
                        <div className="space-y-4">
                            <h4 className="pb-2 text-lg font-bold text-gray-900 border-b border-gray-200">
                                Detail SDM per Unit Organisasi
                            </h4>
                            <div className="grid grid-cols-2 gap-3 md:grid-cols-5">
                                {data.map((item, index) => {
                                    const color =
                                        index === 0
                                            ? CHART_COLORS.primary
                                            : colorArray[
                                                  (index + 1) %
                                                      colorArray.length
                                              ];
                                    return (
                                        <div
                                            key={index}
                                            className="p-3 text-center bg-white border border-gray-100 rounded-lg shadow-sm"
                                        >
                                            <div
                                                className="w-6 h-6 mx-auto mb-2 border-2 border-white rounded shadow-sm"
                                                style={{
                                                    backgroundColor: color,
                                                }}
                                            ></div>
                                            <p className="mb-1 text-xs font-bold text-gray-900 uppercase">
                                                {item.name}
                                            </p>
                                            <p
                                                className="text-lg font-bold"
                                                style={{ color: color }}
                                            >
                                                {item.value || 0}
                                            </p>
                                        </div>
                                    );
                                })}
                            </div>
                        </div>
                    );

                case "provider":
                    return (
                        <div className="space-y-4">
                            <h4 className="pb-2 text-lg font-bold text-gray-900 border-b border-gray-200">
                                Detail SDM per Provider
                            </h4>
                            <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                                {data.map((item, index) => {
                                    const color =
                                        index === 0
                                            ? CHART_COLORS.primary
                                            : colorArray[
                                                  (index + 1) %
                                                      colorArray.length
                                              ];
                                    return (
                                        <div
                                            key={index}
                                            className="flex items-center p-4 bg-white border border-gray-100 rounded-lg shadow-sm"
                                        >
                                            <div
                                                className="w-4 h-4 mr-3 border-2 border-white rounded shadow-sm"
                                                style={{
                                                    backgroundColor: color,
                                                }}
                                            ></div>
                                            <div className="flex-1">
                                                <p className="text-sm font-semibold text-gray-900">
                                                    {item.name}
                                                </p>
                                                <p
                                                    className="text-lg font-bold"
                                                    style={{ color: color }}
                                                >
                                                    {item.value || 0}{" "}
                                                    <span className="text-sm font-normal text-gray-500">
                                                        SDM
                                                    </span>
                                                </p>
                                            </div>
                                        </div>
                                    );
                                })}
                            </div>
                        </div>
                    );

                case "age":
                    return (
                        <div className="space-y-4">
                            <h4 className="pb-2 text-lg font-bold text-gray-900 border-b border-gray-200">
                                Detail Komposisi Usia SDM
                            </h4>
                            <div className="grid grid-cols-2 gap-4 md:grid-cols-4">
                                {data.map((item, index) => {
                                    const percentage =
                                        total > 0
                                            ? (
                                                  ((item.value || 0) / total) *
                                                  100
                                              ).toFixed(1)
                                            : 0;
                                    const color =
                                        index === 0
                                            ? CHART_COLORS.primary
                                            : colorArray[
                                                  (index + 1) %
                                                      colorArray.length
                                              ];
                                    return (
                                        <div
                                            key={index}
                                            className="p-4 text-center bg-white border border-gray-100 rounded-lg shadow-sm"
                                        >
                                            <div
                                                className="flex items-center justify-center w-8 h-8 mx-auto mb-3 text-xs font-bold text-white border-2 border-white rounded-full shadow-sm"
                                                style={{
                                                    backgroundColor: color,
                                                }}
                                            >
                                                {item.name.split("-")[0]}
                                            </div>
                                            <p className="mb-1 text-sm font-semibold text-gray-900">
                                                {item.name} Tahun
                                            </p>
                                            <p
                                                className="text-xl font-bold"
                                                style={{ color: color }}
                                            >
                                                {item.value || 0}
                                            </p>
                                            <p className="text-xs text-gray-500">
                                                ({percentage}%)
                                            </p>
                                        </div>
                                    );
                                })}
                            </div>
                        </div>
                    );

                case "jabatan":
                    return (
                        <div className="space-y-4">
                            <h4 className="pb-2 text-lg font-bold text-gray-900 border-b border-gray-200">
                                Detail Kelompok Jabatan
                            </h4>
                            <div className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                                {data.map((item, index) => {
                                    const percentage =
                                        total > 0
                                            ? (
                                                  ((item.value || 0) / total) *
                                                  100
                                              ).toFixed(1)
                                            : 0;
                                    const color =
                                        index === 0
                                            ? CHART_COLORS.primary
                                            : colorArray[
                                                  (index + 1) %
                                                      colorArray.length
                                              ];
                                    return (
                                        <div
                                            key={index}
                                            className="flex items-center p-4 bg-white border border-gray-100 rounded-lg shadow-sm"
                                        >
                                            <div
                                                className="w-4 h-4 mr-3 border-2 border-white rounded shadow-sm"
                                                style={{
                                                    backgroundColor: color,
                                                }}
                                            ></div>
                                            <div className="flex-1">
                                                <p className="text-sm font-semibold leading-tight text-gray-900 uppercase">
                                                    {item.name}
                                                </p>
                                                <p
                                                    className="mt-1 text-lg font-bold"
                                                    style={{ color: color }}
                                                >
                                                    {item.value || 0}{" "}
                                                    <span className="text-sm font-normal text-gray-500">
                                                        ({percentage}%)
                                                    </span>
                                                </p>
                                            </div>
                                        </div>
                                    );
                                })}
                            </div>
                        </div>
                    );

                default:
                    return (
                        <div className="p-4 text-center">
                            <p className="text-gray-500">
                                Detail tidak tersedia untuk chart ini
                            </p>
                        </div>
                    );
            }
        };

        return (
            <div className="p-6 mt-6 border border-gray-200 shadow-sm bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl">
                {renderDetailContent()}
            </div>
        );
    };

    /**
     * COMPLETELY REWRITTEN: True proportional grid calculation
     */
    const getDynamicGridIntervals = (maxValue) => {
        console.log("GRID CALC: Calculating intervals for maxValue:", maxValue);

        if (maxValue === 0) {
            console.log("GRID CALC: Max value is 0, using minimal intervals");
            return { intervals: [0, 1, 2, 3, 4, 5], max: 5, interval: 1 };
        }

        let interval;
        let max;

        // FIXED: Improved interval calculation for true proportional display
        if (maxValue <= 5) {
            interval = 1;
            max = Math.max(5, Math.ceil(maxValue));
        } else if (maxValue <= 10) {
            interval = 2;
            max = Math.ceil(maxValue / 2) * 2;
        } else if (maxValue <= 25) {
            interval = 5;
            max = Math.ceil(maxValue / 5) * 5;
        } else if (maxValue <= 50) {
            interval = 10;
            max = Math.ceil(maxValue / 10) * 10;
        } else if (maxValue <= 100) {
            interval = 20;
            max = Math.ceil(maxValue / 20) * 20;
        } else if (maxValue <= 250) {
            interval = 25;
            max = Math.ceil(maxValue / 25) * 25;
        } else if (maxValue <= 500) {
            interval = 50;
            max = Math.ceil(maxValue / 50) * 50;
        } else {
            // For very large values
            interval = Math.ceil(maxValue / 10);
            max = Math.ceil(maxValue / interval) * interval;
        }

        // CRITICAL: Ensure max is EXACTLY maxValue for perfect proportions
        max = Math.max(max, maxValue);

        const intervals = [];
        for (let i = 0; i <= max; i += interval) {
            intervals.push(i);
        }

        console.log("GRID CALC: Generated intervals:", {
            intervals,
            max,
            interval,
            maxValue,
            gridLines: intervals.length,
        });
        return { intervals, max, interval };
    };

    // FIXED: True Proportional Bar Chart Component - Clean without labels
    const Enhanced3DBarChart = ({ data, title, description, chartType }) => {
        console.log(`CHART [${chartType}]: Rendering with data:`, data);

        if (!data || data.length === 0) {
            console.log(`CHART [${chartType}]: No data available`);
            return (
                <div>
                    <div className="flex items-center justify-center h-80">
                        <div className="text-center">
                            <div className="inline-flex items-center justify-center w-16 h-16 mb-4 bg-gray-100 rounded-full">
                                <svg
                                    className="w-8 h-8 text-gray-400"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth={2}
                                        d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"
                                    />
                                </svg>
                            </div>
                            <p className="font-medium text-gray-500">
                                Tidak ada data tersedia
                            </p>
                            <p className="mt-1 text-sm text-gray-400">
                                Data akan muncul setelah ada karyawan
                            </p>
                        </div>
                    </div>
                </div>
            );
        }

        // FIXED: Use actual maxValue for TRUE proportional display
        const maxValue = Math.max(...data.map((item) => item.value || 0));
        const hasData = data.some((item) => item.value > 0);

        console.log(`CHART [${chartType}]: Analysis:`, {
            maxValue,
            hasData,
            dataCount: data.length,
            values: data.map((item) => ({
                name: item.name,
                value: item.value,
            })),
        });

        // Handle case where all values are 0
        if (maxValue === 0 || !hasData) {
            console.log(`CHART [${chartType}]: No positive values found`);
            return (
                <div>
                    <div className="h-80">
                        <div className="mb-6 text-center">
                            <h3 className="mb-2 text-xl font-bold text-gray-900">
                                {title}
                            </h3>
                            <p className="text-sm text-gray-600">
                                {description}
                            </p>
                        </div>
                        <div className="flex items-center justify-center h-64">
                            <div className="text-center">
                                <div className="inline-flex items-center justify-center w-12 h-12 mb-4 bg-gray-100 rounded-full">
                                    <svg
                                        className="w-6 h-6 text-gray-400"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                            strokeWidth={2}
                                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                                        />
                                    </svg>
                                </div>
                                <p className="font-medium text-gray-500">
                                    Belum ada data
                                </p>
                                <p className="mt-1 text-sm text-gray-400">
                                    Chart akan menampilkan data saat tersedia
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            );
        }

        const colorArray = Object.values(CHART_COLORS);

        // FIXED: Better height calculation - use maxValue directly for TRUE proportions
        const CHART_HEIGHT = 300; // Increased for better visibility
        const TITLE_SPACE = 80; // Space for title and description
        const VALUE_SPACE = 40; // Space for value labels above bars
        const EFFECTIVE_HEIGHT = CHART_HEIGHT - TITLE_SPACE - VALUE_SPACE;

        console.log(`CHART [${chartType}]: Rendering config:`, {
            maxValue,
            chartHeight: CHART_HEIGHT,
            effectiveHeight: EFFECTIVE_HEIGHT,
            titleSpace: TITLE_SPACE,
            valueSpace: VALUE_SPACE,
            proportionalCalculation: "TRUE PROPORTIONAL TO maxValue",
        });

        return (
            <div>
                <div style={{ height: `${CHART_HEIGHT + TITLE_SPACE}px` }}>
                    {/* Title and description */}
                    <div
                        className="mb-6 text-center"
                        style={{ height: `${TITLE_SPACE}px` }}
                    >
                        <h3 className="mb-2 text-xl font-bold text-gray-900">
                            {title}
                        </h3>
                        <p className="text-sm text-gray-600">{description}</p>
                    </div>

                    {/* FIXED: Chart Container - TRUE proportional system */}
                    <div
                        className="relative bg-white border border-gray-100 rounded-lg"
                        style={{ height: `${CHART_HEIGHT}px` }}
                    >
                        {/* Chart area with MATHEMATICAL precision */}
                        <div
                            className="relative w-full"
                            style={{
                                height: `${CHART_HEIGHT}px`,
                                paddingLeft: "20px",
                                paddingRight: "20px",
                                paddingTop: `${VALUE_SPACE}px`,
                                paddingBottom: "20px",
                            }}
                        >
                            <div
                                className="relative w-full"
                                style={{ height: `${EFFECTIVE_HEIGHT}px` }}
                            >
                                {data.map((item, index) => {
                                    const rawValue = item.value || 0;

                                    // CRITICAL: TRUE proportional height using maxValue as reference
                                    const heightPixels =
                                        maxValue > 0
                                            ? (rawValue / maxValue) *
                                              EFFECTIVE_HEIGHT
                                            : 0;

                                    // Bar width calculation for equal distribution
                                    const containerWidth = 100;
                                    const barWidthPercent = Math.min(
                                        75 / data.length,
                                        15
                                    );
                                    const gapPercent =
                                        (containerWidth -
                                            barWidthPercent * data.length) /
                                        (data.length + 1);
                                    const leftPositionPercent =
                                        gapPercent +
                                        index * (barWidthPercent + gapPercent);

                                    const barColor =
                                        index === 0
                                            ? CHART_COLORS.primary
                                            : colorArray[
                                                  (index + 1) %
                                                      colorArray.length
                                              ];

                                    console.log(
                                        `CHART [${chartType}] Bar ${index} [${item.name}]:`,
                                        {
                                            rawValue,
                                            maxValue,
                                            heightPixels:
                                                Math.round(heightPixels),
                                            percentage:
                                                maxValue > 0
                                                    ? `${(
                                                          (rawValue /
                                                              maxValue) *
                                                          100
                                                      ).toFixed(1)}%`
                                                    : "0%",
                                            leftPosition: `${leftPositionPercent.toFixed(
                                                1
                                            )}%`,
                                            barWidth: `${barWidthPercent.toFixed(
                                                1
                                            )}%`,
                                            TRUE_PROPORTIONAL: true,
                                        }
                                    );

                                    return (
                                        <div
                                            key={index}
                                            className="absolute group"
                                            style={{
                                                left: `${leftPositionPercent}%`,
                                                width: `${barWidthPercent}%`,
                                                bottom: "0px",
                                                height: `${Math.max(
                                                    heightPixels,
                                                    3
                                                )}px`,
                                                zIndex: 10,
                                            }}
                                        >
                                            {/* FIXED: Value Display - ALWAYS visible above bar */}
                                            <div
                                                className="absolute z-30 flex items-center justify-center w-full"
                                                style={{
                                                    top: "-35px", // Fixed position above bar
                                                }}
                                            >
                                                <div className="px-2 py-1 text-xs font-bold text-gray-900 bg-white border border-gray-200 rounded shadow-md whitespace-nowrap">
                                                    {rawValue}
                                                </div>
                                            </div>

                                            {/* Simple Flat Bar - No 3D Effects */}
                                            <div className="relative w-full h-full">
                                                {/* Main Bar - Simple flat design */}
                                                <div
                                                    className="relative w-full transition-all duration-300 ease-out rounded cursor-pointer group-hover:brightness-110"
                                                    style={{
                                                        height: `${Math.max(
                                                            heightPixels,
                                                            3
                                                        )}px`,
                                                        backgroundColor:
                                                            rawValue === 0
                                                                ? `${barColor}40`
                                                                : barColor,
                                                        opacity:
                                                            rawValue === 0
                                                                ? 0.4
                                                                : 1,
                                                    }}
                                                    title={`${item.name}: ${rawValue}`}
                                                >
                                                    {/* Zero value indicator */}
                                                    {rawValue === 0 && (
                                                        <div className="absolute inset-0 flex items-center justify-center">
                                                            <div className="w-full h-0.5 bg-gray-300 rounded" />
                                                        </div>
                                                    )}
                                                </div>
                                            </div>

                                            {/* REMOVED: X-axis label - as requested */}
                                        </div>
                                    );
                                })}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        );
    };

    // FIXED: Enhanced 3D Pie Chart Component - REMOVED DetailContainer calls
    const Enhanced3DPieChart = ({ data, title, description }) => {
        if (!data || data.length === 0) {
            return (
                <div className="overflow-hidden max-h-80">
                    <div className="flex items-center justify-center h-80">
                        <div className="text-center">
                            <div className="inline-flex items-center justify-center w-16 h-16 mb-4 bg-gray-100 rounded-full">
                                <svg
                                    className="w-8 h-8 text-gray-400"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth={2}
                                        d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"
                                    />
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth={2}
                                        d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"
                                    />
                                </svg>
                            </div>
                            <p className="font-medium text-gray-500">
                                Tidak ada data tersedia
                            </p>
                            <p className="mt-1 text-sm text-gray-400">
                                Data akan muncul setelah ada karyawan
                            </p>
                        </div>
                    </div>
                </div>
            );
        }

        const total = data.reduce((sum, item) => sum + (item.value || 0), 0);
        const colors = [
            CHART_COLORS.primary, // Green #439454
            CHART_COLORS.crimson, // Bright red
            CHART_COLORS.navy, // Dark blue
            CHART_COLORS.orange, // Bright orange
        ];

        // Handle case where total is 0
        if (total === 0) {
            return (
                <div className="overflow-hidden max-h-80">
                    <div className="h-80">
                        <div className="mb-6 text-center">
                            <h3 className="mb-2 text-xl font-bold text-gray-900">
                                {title}
                            </h3>
                            <p className="text-sm text-gray-600">
                                {description}
                            </p>
                        </div>
                        <div className="flex items-center justify-center h-64">
                            <div className="text-center">
                                <div className="inline-flex items-center justify-center w-12 h-12 mb-4 bg-gray-100 rounded-full">
                                    <svg
                                        className="w-6 h-6 text-gray-400"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                            strokeWidth={2}
                                            d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"
                                        />
                                    </svg>
                                </div>
                                <p className="font-medium text-gray-500">
                                    Belum ada data
                                </p>
                                <p className="mt-1 text-sm text-gray-400">
                                    Chart akan menampilkan data saat tersedia
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            );
        }

        return (
            <div className="overflow-hidden max-h-80">
                <div className="h-80">
                    {/* Title positioned like Excel charts */}
                    <div className="mb-6 text-center">
                        <h3 className="mb-2 text-xl font-bold text-gray-900">
                            {title}
                        </h3>
                        <p className="text-sm text-gray-600">{description}</p>
                    </div>

                    <div
                        className="flex items-center justify-center"
                        style={{ height: "240px", maxHeight: "240px" }}
                    >
                        <div className="relative flex items-center">
                            {/* 3D Pie Chart Container */}
                            <div className="relative">
                                {/* Shadow */}
                                <div className="absolute w-48 h-48 bg-black rounded-full top-2 left-2 opacity-20 blur-md"></div>

                                {/* Main Pie */}
                                <div className="relative w-48 h-48 overflow-hidden border-4 border-white rounded-full shadow-2xl">
                                    <svg
                                        viewBox="0 0 42 42"
                                        className="w-full h-full transform -rotate-90"
                                    >
                                        {data.map((item, index) => {
                                            const value = item.value || 0;
                                            const percentage =
                                                total > 0
                                                    ? (value / total) * 100
                                                    : 0;
                                            const offset = data
                                                .slice(0, index)
                                                .reduce(
                                                    (acc, curr) =>
                                                        acc +
                                                        (total > 0
                                                            ? ((curr.value ||
                                                                  0) /
                                                                  total) *
                                                              100
                                                            : 0),
                                                    0
                                                );
                                            const color =
                                                colors[index % colors.length];

                                            if (percentage === 0) return null;

                                            return (
                                                <circle
                                                    key={index}
                                                    cx="21"
                                                    cy="21"
                                                    r="15.91549430918954"
                                                    fill="transparent"
                                                    stroke={color}
                                                    strokeWidth="3"
                                                    strokeDasharray={`${percentage} ${
                                                        100 - percentage
                                                    }`}
                                                    strokeDashoffset={-offset}
                                                    className="transition-all duration-1000 cursor-pointer hover:stroke-8"
                                                    style={{
                                                        filter: `drop-shadow(0 2px 4px ${color}40)`,
                                                    }}
                                                >
                                                    <title>{`${
                                                        item.name
                                                    }: ${value} (${percentage.toFixed(
                                                        1
                                                    )}%)`}</title>
                                                </circle>
                                            );
                                        })}
                                    </svg>

                                    {/* Center Circle with Total */}
                                    <div className="absolute inset-0 flex items-center justify-center">
                                        <div className="flex items-center justify-center w-24 h-24 bg-white border-2 border-gray-100 rounded-full shadow-inner">
                                            <div className="text-center">
                                                <div className="text-2xl font-bold text-gray-900">
                                                    {total}
                                                </div>
                                                <div className="text-xs text-gray-500">
                                                    Total
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        );
    };

    return (
        <DashboardLayout title="Dashboard SDM">
            <Head title="Dashboard SDM GAPURA ANGKASA" />

            <div className="min-h-screen bg-gradient-to-br from-gray-50 via-white to-gray-50">
                {/* Header Section */}
                <div className="relative overflow-hidden bg-white shadow-sm">
                    <div className="absolute inset-0 bg-gradient-to-r from-green-500/5 to-transparent"></div>
                    <div className="relative px-8 py-10">
                        <div className="max-w-4xl">
                            <div className="flex items-center justify-between">
                                <div>
                                    <h1 className="mb-3 text-4xl font-bold text-gray-900">
                                        Dashboard SDM GAPURA ANGKASA
                                    </h1>
                                    <p className="mb-6 text-lg text-gray-600">
                                        Sistem Manajemen Sumber Daya Manusia
                                        Bandar Udara Ngurah Rai
                                    </p>
                                </div>

                                {/* Manual Refresh Button */}
                                <div className="flex items-center space-x-4">
                                    <button
                                        onClick={manualRefresh}
                                        disabled={isRefreshing}
                                        className="inline-flex items-center px-4 py-2 text-sm font-medium text-white transition-all duration-200 rounded-lg shadow-sm bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
                                    >
                                        <svg
                                            className={`w-4 h-4 mr-2 ${
                                                isRefreshing
                                                    ? "animate-spin"
                                                    : ""
                                            }`}
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                strokeLinecap="round"
                                                strokeLinejoin="round"
                                                strokeWidth={2}
                                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                                            />
                                        </svg>
                                        {isRefreshing
                                            ? "Memperbarui..."
                                            : "Perbarui Data"}
                                    </button>
                                </div>
                            </div>

                            <div className="inline-flex items-center px-4 py-3 border border-orange-200 shadow-sm bg-gradient-to-r from-orange-50 to-orange-100 rounded-xl">
                                <div className="flex items-center justify-center w-8 h-8 mr-3 bg-orange-500 rounded-full">
                                    <svg
                                        className="w-4 h-4 text-white"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                            strokeWidth={2}
                                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                        />
                                    </svg>
                                </div>
                                <span className="font-medium text-orange-800">
                                    Dashboard Real-time: Data akan diperbarui
                                    otomatis saat ada perubahan karyawan atau
                                    klik tombol "Perbarui Data"
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Statistics Cards */}
                <div className="px-8 pb-8 -mt-4">
                    <div className="relative z-10 grid grid-cols-1 gap-8 mb-16 md:grid-cols-2 lg:grid-cols-4">
                        {/* Total Karyawan */}
                        <div className="relative overflow-hidden transition-all duration-700 transform bg-white border border-gray-100 shadow-xl stats-card group rounded-3xl hover:shadow-2xl hover:-translate-y-4 hover:scale-105">
                            <div className="absolute inset-0 transition-all duration-700 opacity-0 bg-gradient-to-br from-blue-500/10 to-blue-600/20 group-hover:opacity-100"></div>
                            <div className="relative p-8">
                                <div className="flex items-center justify-between mb-8">
                                    <div
                                        style={{ backgroundColor: BASE_COLOR }}
                                        className="flex items-center justify-center transition-all duration-700 shadow-2xl w-18 h-18 rounded-3xl group-hover:scale-125 group-hover:rotate-6"
                                    >
                                        <svg
                                            className="text-white transition-all duration-700 w-9 h-9 group-hover:scale-110"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                strokeLinecap="round"
                                                strokeLinejoin="round"
                                                strokeWidth={2}
                                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                                            />
                                        </svg>
                                    </div>
                                </div>
                                <div className="transition-all duration-700">
                                    <p className="mb-3 text-4xl font-bold text-gray-900 transition-all duration-700">
                                        {loading ? (
                                            <div className="w-20 h-12 bg-gray-200 rounded animate-pulse"></div>
                                        ) : (
                                            stats.total_employees
                                        )}
                                    </p>
                                    <p className="mb-2 text-lg font-semibold text-gray-600">
                                        Total Karyawan
                                    </p>
                                    <p
                                        className="inline-block px-3 py-1 text-sm font-medium text-white rounded-full"
                                        style={{ backgroundColor: BASE_COLOR }}
                                    >
                                        Seluruh karyawan
                                    </p>
                                </div>
                            </div>
                        </div>

                        {/* Pegawai Aktif */}
                        <div className="relative overflow-hidden transition-all duration-700 transform bg-white border border-gray-100 shadow-xl stats-card group rounded-3xl hover:shadow-2xl hover:-translate-y-4 hover:scale-105">
                            <div className="absolute inset-0 transition-all duration-700 opacity-0 bg-gradient-to-br from-green-500/10 to-green-600/20 group-hover:opacity-100"></div>
                            <div className="relative p-8">
                                <div className="flex items-center justify-between mb-8">
                                    <div className="flex items-center justify-center transition-all duration-700 shadow-2xl w-18 h-18 bg-gradient-to-br from-green-500 to-green-600 rounded-3xl group-hover:scale-125 group-hover:rotate-6">
                                        <svg
                                            className="text-white transition-all duration-700 w-9 h-9 group-hover:scale-110"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                strokeLinecap="round"
                                                strokeLinejoin="round"
                                                strokeWidth={2}
                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                                            />
                                        </svg>
                                    </div>
                                </div>
                                <div className="transition-all duration-700">
                                    <p className="mb-3 text-4xl font-bold text-gray-900 transition-all duration-700">
                                        {loading ? (
                                            <div className="w-20 h-12 bg-gray-200 rounded animate-pulse"></div>
                                        ) : (
                                            stats.active_employees
                                        )}
                                    </p>
                                    <p className="mb-2 text-lg font-semibold text-gray-600">
                                        Pegawai Aktif
                                    </p>
                                    <p className="inline-block px-3 py-1 text-sm font-medium text-green-600 rounded-full bg-green-50">
                                        Status aktif
                                    </p>
                                </div>
                            </div>
                        </div>

                        {/* Pegawai Tetap */}
                        <div className="relative overflow-hidden transition-all duration-700 transform bg-white border border-gray-100 shadow-xl stats-card group rounded-3xl hover:shadow-2xl hover:-translate-y-4 hover:scale-105">
                            <div className="absolute inset-0 transition-all duration-700 opacity-0 bg-gradient-to-br from-orange-500/10 to-orange-600/20 group-hover:opacity-100"></div>
                            <div className="relative p-8">
                                <div className="flex items-center justify-between mb-8">
                                    <div className="flex items-center justify-center transition-all duration-700 shadow-2xl w-18 h-18 bg-gradient-to-br from-orange-500 to-orange-600 rounded-3xl group-hover:scale-125 group-hover:rotate-6">
                                        <svg
                                            className="text-white transition-all duration-700 w-9 h-9 group-hover:scale-110"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                strokeLinecap="round"
                                                strokeLinejoin="round"
                                                strokeWidth={2}
                                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                                            />
                                        </svg>
                                    </div>
                                </div>
                                <div className="transition-all duration-700">
                                    <p className="mb-3 text-4xl font-bold text-gray-900 transition-all duration-700">
                                        {loading ? (
                                            <div className="w-20 h-12 bg-gray-200 rounded animate-pulse"></div>
                                        ) : (
                                            stats.pegawai_tetap
                                        )}
                                    </p>
                                    <p className="mb-2 text-lg font-semibold text-gray-600">
                                        Pegawai Tetap
                                    </p>
                                    <p className="inline-block px-3 py-1 text-sm font-medium text-orange-600 rounded-full bg-orange-50">
                                        Pegawai tetap
                                    </p>
                                </div>
                            </div>
                        </div>

                        {/* TAD */}
                        <div className="relative overflow-hidden transition-all duration-700 transform bg-white border border-gray-100 shadow-xl stats-card group rounded-3xl hover:shadow-2xl hover:-translate-y-4 hover:scale-105">
                            <div className="absolute inset-0 transition-all duration-700 opacity-0 bg-gradient-to-br from-purple-500/10 to-purple-600/20 group-hover:opacity-100"></div>
                            <div className="relative p-8">
                                <div className="flex items-center justify-between mb-8">
                                    <div className="flex items-center justify-center transition-all duration-700 shadow-2xl w-18 h-18 bg-gradient-to-br from-purple-500 to-purple-600 rounded-3xl group-hover:scale-125 group-hover:rotate-6">
                                        <svg
                                            className="text-white transition-all duration-700 w-9 h-9 group-hover:scale-110"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                strokeLinecap="round"
                                                strokeLinejoin="round"
                                                strokeWidth={2}
                                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                            />
                                        </svg>
                                    </div>
                                </div>
                                <div className="transition-all duration-700">
                                    <p className="mb-3 text-4xl font-bold text-gray-900 transition-all duration-700">
                                        {loading ? (
                                            <div className="w-20 h-12 bg-gray-200 rounded animate-pulse"></div>
                                        ) : (
                                            stats.tad_total
                                        )}
                                    </p>
                                    <p className="mb-2 text-lg font-semibold text-gray-600">
                                        TAD
                                    </p>
                                    <p className="inline-block px-3 py-1 text-sm font-medium text-purple-600 rounded-full bg-purple-50">
                                        Tenaga Alih Daya
                                    </p>
                                    {/* TAD Breakdown */}
                                    <div className="pt-4 mt-4 border-t border-gray-100">
                                        <div className="space-y-2">
                                            <div className="flex items-center justify-between text-sm">
                                                <span className="text-gray-600">
                                                    Paket SDM:
                                                </span>
                                                <span className="font-semibold text-gray-900">
                                                    {loading ? (
                                                        <div className="w-8 h-4 bg-gray-200 rounded animate-pulse"></div>
                                                    ) : (
                                                        stats.tad_paket_sdm || 0
                                                    )}
                                                </span>
                                            </div>
                                            <div className="flex items-center justify-between text-sm">
                                                <span className="text-gray-600">
                                                    Paket Pekerjaan:
                                                </span>
                                                <span className="font-semibold text-gray-900">
                                                    {loading ? (
                                                        <div className="w-8 h-4 bg-gray-200 rounded animate-pulse"></div>
                                                    ) : (
                                                        stats.tad_paket_pekerjaan ||
                                                        0
                                                    )}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Enhanced Charts Section */}
                    <div className="space-y-16">
                        {/* Chart Row 1: Gender & Status */}
                        <div className="grid grid-cols-1 gap-12 xl:grid-cols-2">
                            {/* Jenis Kelamin Chart */}
                            <div className="relative p-10 overflow-hidden transition-all duration-500 bg-white border border-gray-100 shadow-xl rounded-3xl hover:shadow-2xl hover:-translate-y-2 animate-fadeInUp">
                                <div className="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-green-400 via-green-500 to-green-600"></div>
                                {loading ? (
                                    <div className="flex items-center justify-center h-96">
                                        <div className="text-center">
                                            <div className="inline-block w-12 h-12 border-4 border-green-200 rounded-full border-t-green-600 animate-spin"></div>
                                            <p className="mt-4 text-gray-500">
                                                Memuat data jenis kelamin...
                                            </p>
                                        </div>
                                    </div>
                                ) : (
                                    <div>
                                        <Enhanced3DPieChart
                                            data={chartData.gender}
                                            title="Jenis Kelamin"
                                            description="Distribusi berdasarkan jenis kelamin"
                                        />
                                        <DetailContainer
                                            data={chartData.gender}
                                            chartType="gender"
                                            title="Jenis Kelamin"
                                        />
                                    </div>
                                )}
                            </div>

                            {/* Status Pegawai Chart */}
                            <div
                                className="relative p-10 overflow-hidden transition-all duration-500 bg-white border border-gray-100 shadow-xl rounded-3xl hover:shadow-2xl hover:-translate-y-2 animate-fadeInUp"
                                style={{ animationDelay: "0.1s" }}
                            >
                                <div className="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-blue-400 via-blue-500 to-blue-600"></div>
                                {loading ? (
                                    <div className="flex items-center justify-center h-96">
                                        <div className="text-center">
                                            <div className="inline-block w-12 h-12 border-4 border-blue-200 rounded-full border-t-blue-600 animate-spin"></div>
                                            <p className="mt-4 text-gray-500">
                                                Memuat data status pegawai...
                                            </p>
                                        </div>
                                    </div>
                                ) : (
                                    <div>
                                        <Enhanced3DBarChart
                                            data={chartData.status}
                                            title="Status Pegawai"
                                            description="Distribusi berdasarkan status pegawai"
                                            chartType="status"
                                        />
                                        <DetailContainer
                                            data={chartData.status}
                                            chartType="status"
                                            title="Status Pegawai"
                                        />
                                    </div>
                                )}
                            </div>
                        </div>

                        {/* Chart Row 2: SDM per Unit - Full Width */}
                        <div className="w-full">
                            <div
                                className="relative p-10 overflow-hidden transition-all duration-500 bg-white border border-gray-100 shadow-xl rounded-3xl hover:shadow-2xl hover:-translate-y-2 animate-fadeInUp"
                                style={{ animationDelay: "0.2s" }}
                            >
                                <div className="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-purple-400 via-purple-500 to-purple-600"></div>
                                {loading ? (
                                    <div className="flex items-center justify-center h-96">
                                        <div className="text-center">
                                            <div className="inline-block w-12 h-12 border-4 border-purple-200 rounded-full border-t-purple-600 animate-spin"></div>
                                            <p className="mt-4 text-gray-500">
                                                Memuat data per unit...
                                            </p>
                                        </div>
                                    </div>
                                ) : (
                                    <div>
                                        <Enhanced3DBarChart
                                            data={chartData.unit}
                                            title="SDM per Unit"
                                            description="Distribusi berdasarkan unit organisasi"
                                            chartType="unit"
                                        />
                                        <DetailContainer
                                            data={chartData.unit}
                                            chartType="unit"
                                            title="SDM per Unit"
                                        />
                                    </div>
                                )}
                            </div>
                        </div>

                        {/* Chart Row 3: SDM per Provider - Full Width */}
                        <div className="w-full">
                            <div
                                className="relative p-10 overflow-hidden transition-all duration-500 bg-white border border-gray-100 shadow-xl rounded-3xl hover:shadow-2xl hover:-translate-y-2 animate-fadeInUp"
                                style={{ animationDelay: "0.3s" }}
                            >
                                <div className="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-orange-400 via-orange-500 to-orange-600"></div>
                                {loading ? (
                                    <div className="flex items-center justify-center h-96">
                                        <div className="text-center">
                                            <div className="inline-block w-12 h-12 border-4 border-orange-200 rounded-full border-t-orange-600 animate-spin"></div>
                                            <p className="mt-4 text-gray-500">
                                                Memuat data per provider...
                                            </p>
                                        </div>
                                    </div>
                                ) : (
                                    <div>
                                        <Enhanced3DBarChart
                                            data={chartData.provider}
                                            title="SDM per Provider"
                                            description="Distribusi berdasarkan perusahaan provider"
                                            chartType="provider"
                                        />
                                        <DetailContainer
                                            data={chartData.provider}
                                            chartType="provider"
                                            title="SDM per Provider"
                                        />
                                    </div>
                                )}
                            </div>
                        </div>

                        {/* Chart Row 4: Age & Position */}
                        <div className="grid grid-cols-1 gap-12 xl:grid-cols-2">
                            {/* Komposisi Usia Chart */}
                            <div
                                className="relative p-10 overflow-hidden transition-all duration-500 bg-white border border-gray-100 shadow-xl rounded-3xl hover:shadow-2xl hover:-translate-y-2 animate-fadeInUp"
                                style={{ animationDelay: "0.4s" }}
                            >
                                <div className="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-teal-400 via-teal-500 to-teal-600"></div>
                                {loading ? (
                                    <div className="flex items-center justify-center h-96">
                                        <div className="text-center">
                                            <div className="inline-block w-12 h-12 border-4 border-teal-200 rounded-full border-t-teal-600 animate-spin"></div>
                                            <p className="mt-4 text-gray-500">
                                                Memuat data komposisi usia...
                                            </p>
                                        </div>
                                    </div>
                                ) : (
                                    <div>
                                        <Enhanced3DBarChart
                                            data={chartData.age}
                                            title="Komposisi Usia SDM"
                                            description="Distribusi berdasarkan kelompok usia"
                                            chartType="age"
                                        />
                                        <DetailContainer
                                            data={chartData.age}
                                            chartType="age"
                                            title="Komposisi Usia SDM"
                                        />
                                    </div>
                                )}
                            </div>

                            {/* Kelompok Jabatan Chart */}
                            <div
                                className="relative p-10 overflow-hidden transition-all duration-500 bg-white border border-gray-100 shadow-xl rounded-3xl hover:shadow-2xl hover:-translate-y-2 animate-fadeInUp"
                                style={{ animationDelay: "0.5s" }}
                            >
                                <div className="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-indigo-400 via-indigo-500 to-indigo-600"></div>
                                {loading ? (
                                    <div className="flex items-center justify-center h-96">
                                        <div className="text-center">
                                            <div className="inline-block w-12 h-12 border-4 border-indigo-200 rounded-full border-t-indigo-600 animate-spin"></div>
                                            <p className="mt-4 text-gray-500">
                                                Memuat data kelompok jabatan...
                                            </p>
                                        </div>
                                    </div>
                                ) : (
                                    <div>
                                        <Enhanced3DBarChart
                                            data={chartData.jabatan}
                                            title="Kelompok Jabatan"
                                            description="Distribusi berdasarkan kelompok jabatan"
                                            chartType="jabatan"
                                        />
                                        <DetailContainer
                                            data={chartData.jabatan}
                                            chartType="jabatan"
                                            title="Kelompok Jabatan"
                                        />
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>

                    {/* Enhanced Last Update Indicator */}
                    <div className="flex items-center justify-center mt-16">
                        <div className="inline-flex items-center px-6 py-3 text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-full shadow-lg">
                            <div className="flex items-center mr-3">
                                <div className="w-3 h-3 bg-green-500 rounded-full"></div>
                                <div className="w-2 h-2 ml-1 bg-green-300 rounded-full"></div>
                                <div className="w-1 h-1 ml-1 bg-green-200 rounded-full"></div>
                            </div>
                            <span className="font-semibold">
                                Real-time Data
                            </span>
                            <span className="mx-2 text-gray-400">•</span>
                            <span>
                                Terakhir diperbarui:{" "}
                                {new Date(lastUpdate).toLocaleString("id-ID", {
                                    timeZone: "Asia/Makassar",
                                    year: "numeric",
                                    month: "short",
                                    day: "numeric",
                                    hour: "2-digit",
                                    minute: "2-digit",
                                    second: "2-digit",
                                })}{" "}
                                WITA
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {/* Fixed JSX Style */}
            <style>{`
                @keyframes fadeInUp {
                    from {
                        opacity: 0;
                        transform: translateY(30px);
                    }
                    to {
                        opacity: 1;
                        transform: translateY(0);
                    }
                }

                .animate-fadeInUp {
                    animation: fadeInUp 0.8s ease-out forwards;
                }

                .stats-card {
                    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
                }

                .stats-card:hover {
                    transform: translateY(-8px) scale(1.02);
                    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
                }

                @keyframes chartLoad {
                    from {
                        opacity: 0;
                        transform: scale(0.8);
                    }
                    to {
                        opacity: 1;
                        transform: scale(1);
                    }
                }

                .chart-container {
                    animation: chartLoad 0.6s ease-out forwards;
                }
            `}</style>
        </DashboardLayout>
    );
}
