// resources/js/Components/EmployeeNotification.jsx
import React, { useState, useEffect } from "react";
import { CheckCircle, X, Clock, Users, TrendingUp } from "lucide-react";

const EmployeeNotification = ({
    newEmployees = [],
    onDismiss,
    timezone = "Asia/Makassar", // WITA timezone
}) => {
    const [isVisible, setIsVisible] = useState(false);
    const [dismissedIds, setDismissedIds] = useState(new Set());

    useEffect(() => {
        // Load dismissed notifications from localStorage
        const dismissed = JSON.parse(
            localStorage.getItem("dismissedEmployeeNotifications") || "[]"
        );
        setDismissedIds(new Set(dismissed));
    }, []);

    useEffect(() => {
        if (newEmployees.length > 0) {
            setIsVisible(true);
        }
    }, [newEmployees]);

    // Filter out dismissed notifications
    const visibleEmployees = newEmployees.filter(
        (emp) => !dismissedIds.has(emp.id)
    );

    // Get time-based greeting for WITA timezone
    const getTimeBasedInfo = () => {
        const now = new Date();
        const witaTime = new Intl.DateTimeFormat("id-ID", {
            timeZone: timezone,
            hour: "2-digit",
            minute: "2-digit",
            day: "2-digit",
            month: "long",
            year: "numeric",
        }).format(now);

        const hour = parseInt(
            new Intl.DateTimeFormat("id-ID", {
                timeZone: timezone,
                hour: "2-digit",
                hour12: false,
            }).format(now)
        );

        let greeting = "Hari ini";
        let icon = Clock;
        let bgColor = "from-blue-500 to-blue-600";

        if (hour >= 5 && hour < 12) {
            greeting = "Pagi ini";
            icon = TrendingUp;
            bgColor = "from-orange-500 to-orange-600";
        } else if (hour >= 12 && hour < 17) {
            greeting = "Siang ini";
            icon = Users;
            bgColor = "from-green-500 to-green-600";
        } else if (hour >= 17 && hour < 21) {
            greeting = "Sore ini";
            icon = CheckCircle;
            bgColor = "from-purple-500 to-purple-600";
        } else {
            greeting = "Malam ini";
            icon = Clock;
            bgColor = "from-indigo-500 to-indigo-600";
        }

        return { greeting, icon: icon, bgColor, witaTime };
    };

    const handleDismiss = (employeeId = null) => {
        if (employeeId) {
            // Dismiss specific employee notification
            const newDismissed = new Set([...dismissedIds, employeeId]);
            setDismissedIds(newDismissed);
            localStorage.setItem(
                "dismissedEmployeeNotifications",
                JSON.stringify([...newDismissed])
            );
        } else {
            // Dismiss all
            const allIds = visibleEmployees.map((emp) => emp.id);
            const newDismissed = new Set([...dismissedIds, ...allIds]);
            setDismissedIds(newDismissed);
            localStorage.setItem(
                "dismissedEmployeeNotifications",
                JSON.stringify([...newDismissed])
            );
            setIsVisible(false);
        }

        if (onDismiss) onDismiss(employeeId);
    };

    const clearDismissedNotifications = () => {
        setDismissedIds(new Set());
        localStorage.removeItem("dismissedEmployeeNotifications");
    };

    if (!isVisible || visibleEmployees.length === 0) return null;

    const timeInfo = getTimeBasedInfo();
    const IconComponent = timeInfo.icon;

    return (
        <div className="fixed z-50 max-w-md top-4 right-4">
            <div className="bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden transform transition-all duration-500 hover:scale-[1.02]">
                {/* Header */}
                <div
                    className={`bg-gradient-to-r ${timeInfo.bgColor} px-6 py-4`}
                >
                    <div className="flex items-center justify-between">
                        <div className="flex items-center space-x-3">
                            <div className="p-2 rounded-full bg-white/20 backdrop-blur-sm">
                                <IconComponent className="w-5 h-5 text-white" />
                            </div>
                            <div>
                                <h3 className="text-lg font-semibold text-white">
                                    {timeInfo.greeting}
                                </h3>
                                <p className="text-sm text-white/80">
                                    {visibleEmployees.length} karyawan baru
                                </p>
                            </div>
                        </div>
                        <button
                            onClick={() => handleDismiss()}
                            className="p-1 transition-colors rounded-full text-white/80 hover:text-white hover:bg-white/20"
                        >
                            <X className="w-5 h-5" />
                        </button>
                    </div>
                </div>

                {/* Content */}
                <div className="px-6 py-4 overflow-y-auto max-h-64">
                    <div className="space-y-3">
                        {visibleEmployees.slice(0, 3).map((employee) => (
                            <div
                                key={employee.id}
                                className="flex items-center justify-between p-3 bg-gray-50 rounded-xl hover:bg-[#439454]/5 transition-colors group"
                            >
                                <div className="flex items-center space-x-3">
                                    <div className="w-10 h-10 bg-[#439454] rounded-full flex items-center justify-center text-white font-semibold text-sm">
                                        {employee.nama_lengkap?.charAt(0) ||
                                            "K"}
                                    </div>
                                    <div>
                                        <p className="text-sm font-medium text-gray-900">
                                            {employee.nama_lengkap}
                                        </p>
                                        <p className="text-xs text-gray-500">
                                            NIP: {employee.nip} •{" "}
                                            {employee.unit_organisasi}
                                        </p>
                                    </div>
                                </div>
                                <button
                                    onClick={() => handleDismiss(employee.id)}
                                    className="p-1 text-gray-400 transition-opacity opacity-0 group-hover:opacity-100 hover:text-gray-600"
                                >
                                    <X className="w-4 h-4" />
                                </button>
                            </div>
                        ))}

                        {visibleEmployees.length > 3 && (
                            <div className="py-2 text-center">
                                <p className="text-sm text-gray-500">
                                    dan {visibleEmployees.length - 3} karyawan
                                    lainnya
                                </p>
                            </div>
                        )}
                    </div>
                </div>

                {/* Footer */}
                <div className="px-6 py-3 border-t border-gray-100 bg-gray-50">
                    <div className="flex items-center justify-between">
                        <p className="text-xs text-gray-500">
                            WITA: {timeInfo.witaTime}
                        </p>
                        <div className="flex space-x-2">
                            <button
                                onClick={clearDismissedNotifications}
                                className="text-xs text-[#439454] hover:text-[#439454]/80 font-medium"
                            >
                                Reset
                            </button>
                            <button
                                onClick={() => handleDismiss()}
                                className="text-xs bg-[#439454] text-white px-3 py-1 rounded-full hover:bg-[#439454]/90 transition-colors"
                            >
                                Tutup
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default EmployeeNotification;
