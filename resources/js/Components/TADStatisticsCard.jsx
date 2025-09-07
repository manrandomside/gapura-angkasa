import React from "react";
import { Users, Clock, Briefcase, TrendingUp } from "lucide-react";

const TADStatisticsCard = ({ statistics = {} }) => {
    const {
        tad_total = 0,
        tad_paket_sdm = 0,
        tad_paket_pekerjaan = 0,
    } = statistics;

    return (
        <div className="overflow-hidden transition-all duration-300 bg-white border border-gray-100 shadow-lg rounded-2xl hover:shadow-xl">
            {/* Header dengan gradient */}
            <div className="px-6 py-4 bg-gradient-to-r from-yellow-500 to-orange-600">
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-3">
                        <div className="p-2 rounded-lg bg-white/20">
                            <Clock className="w-6 h-6 text-white" />
                        </div>
                        <div>
                            <h3 className="text-lg font-bold text-white">
                                TAD
                            </h3>
                            <p className="text-sm text-orange-100">
                                Tenaga Alih Daya
                            </p>
                        </div>
                    </div>
                    <div className="text-right">
                        <div className="text-3xl font-bold text-white">
                            {tad_total}
                        </div>
                        <div className="text-sm text-orange-100">Total</div>
                        {/* Total breakdown in header */}
                        <div className="mt-2 text-xs text-orange-100">
                            <div>Paket SDM: {tad_paket_sdm}</div>
                            <div>Paket Pekerjaan: {tad_paket_pekerjaan}</div>
                        </div>
                    </div>
                </div>
            </div>

            {/* Body dengan breakdown TAD */}
            <div className="p-6">
                <div className="space-y-4">
                    {/* TAD Paket SDM */}
                    <div className="flex items-center justify-between p-4 border border-yellow-200 bg-yellow-50 rounded-xl">
                        <div className="flex items-center gap-3">
                            <div className="p-2 bg-yellow-500 rounded-lg">
                                <Users className="w-5 h-5 text-white" />
                            </div>
                            <div>
                                <h4 className="font-semibold text-gray-800">
                                    TAD Paket SDM
                                </h4>
                                <p className="text-sm text-gray-600">
                                    Sumber Daya Manusia
                                </p>
                            </div>
                        </div>
                        <div className="text-right">
                            <div className="text-2xl font-bold text-yellow-600">
                                {tad_paket_sdm}
                            </div>
                            <div className="text-sm text-gray-500">
                                {tad_total > 0
                                    ? `${Math.round(
                                          (tad_paket_sdm / tad_total) * 100
                                      )}%`
                                    : "0%"}
                            </div>
                        </div>
                    </div>

                    {/* TAD Paket Pekerjaan */}
                    <div className="flex items-center justify-between p-4 border border-orange-200 bg-orange-50 rounded-xl">
                        <div className="flex items-center gap-3">
                            <div className="p-2 bg-orange-500 rounded-lg">
                                <Briefcase className="w-5 h-5 text-white" />
                            </div>
                            <div>
                                <h4 className="font-semibold text-gray-800">
                                    TAD Paket Pekerjaan
                                </h4>
                                <p className="text-sm text-gray-600">
                                    Kontrak Pekerjaan
                                </p>
                            </div>
                        </div>
                        <div className="text-right">
                            <div className="text-2xl font-bold text-orange-600">
                                {tad_paket_pekerjaan}
                            </div>
                            <div className="text-sm text-gray-500">
                                {tad_total > 0
                                    ? `${Math.round(
                                          (tad_paket_pekerjaan / tad_total) *
                                              100
                                      )}%`
                                    : "0%"}
                            </div>
                        </div>
                    </div>
                </div>

                {/* Progress bar visual */}
                {tad_total > 0 && (
                    <div className="mt-6">
                        <div className="mb-2 text-sm font-medium text-gray-700">
                            Distribusi TAD
                        </div>
                        <div className="flex h-3 overflow-hidden bg-gray-200 rounded-full">
                            <div
                                className="transition-all duration-500 bg-yellow-500"
                                style={{
                                    width: `${
                                        (tad_paket_sdm / tad_total) * 100
                                    }%`,
                                }}
                            ></div>
                            <div
                                className="transition-all duration-500 bg-orange-500"
                                style={{
                                    width: `${
                                        (tad_paket_pekerjaan / tad_total) * 100
                                    }%`,
                                }}
                            ></div>
                        </div>
                        <div className="flex justify-between mt-2 text-xs text-gray-600">
                            <span>Paket SDM</span>
                            <span>Paket Pekerjaan</span>
                        </div>
                    </div>
                )}

                {/* Quick info footer */}
                <div className="pt-4 mt-6 border-t border-gray-100">
                    <div className="flex items-center justify-between text-sm">
                        <div className="flex items-center gap-1 text-gray-600">
                            <TrendingUp className="w-4 h-4" />
                            <span>Status Tenaga Alih Daya</span>
                        </div>
                        <div className="text-gray-500">
                            {tad_total > 0 ? "Aktif" : "Tidak ada data"}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default TADStatisticsCard;
