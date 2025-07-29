import { Head, Link } from "@inertiajs/react";

export default function Error404() {
    return (
        <>
            <Head title="404 - Halaman Tidak Ditemukan" />
            <div className="flex items-center justify-center min-h-screen bg-gray-50">
                <div className="max-w-md mx-auto text-center">
                    <div className="mb-8">
                        <div className="flex items-center justify-center w-20 h-20 mx-auto mb-4 bg-red-100 rounded-full">
                            <svg
                                className="w-10 h-10 text-red-600"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    strokeWidth={2}
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"
                                />
                            </svg>
                        </div>
                        <h1 className="mb-2 text-4xl font-bold text-gray-800">
                            404
                        </h1>
                        <h2 className="mb-4 text-xl font-semibold text-gray-600">
                            Halaman Tidak Ditemukan
                        </h2>
                        <p className="mb-8 text-gray-500">
                            Maaf, halaman yang Anda cari tidak dapat ditemukan.
                            Mungkin halaman tersebut telah dipindahkan atau
                            dihapus.
                        </p>
                    </div>

                    <div className="space-y-4">
                        <Link
                            href="/dashboard"
                            className="inline-flex items-center px-6 py-3 text-white bg-[#439454] rounded-lg hover:bg-[#358945] transition-colors duration-200"
                        >
                            <svg
                                className="w-5 h-5 mr-2"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    strokeWidth={2}
                                    d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2 2z"
                                />
                                <path
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    strokeWidth={2}
                                    d="M8 5a2 2 0 012-2h4a2 2 0 012 2v2H8V5z"
                                />
                            </svg>
                            Kembali ke Dashboard
                        </Link>

                        <div className="text-sm text-gray-500">
                            Atau gunakan menu navigasi di bawah ini:
                        </div>

                        <div className="grid grid-cols-2 gap-3 mt-4">
                            <Link
                                href="/data-karyawan"
                                className="flex items-center justify-center px-4 py-2 text-sm text-gray-600 transition-colors duration-200 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
                            >
                                <svg
                                    className="w-4 h-4 mr-2"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth={2}
                                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"
                                    />
                                </svg>
                                Data Karyawan
                            </Link>

                            <Link
                                href="/organisasi"
                                className="flex items-center justify-center px-4 py-2 text-sm text-gray-600 transition-colors duration-200 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
                            >
                                <svg
                                    className="w-4 h-4 mr-2"
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
                                Organisasi
                            </Link>

                            <Link
                                href="/laporan"
                                className="flex items-center justify-center px-4 py-2 text-sm text-gray-600 transition-colors duration-200 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
                            >
                                <svg
                                    className="w-4 h-4 mr-2"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth={2}
                                        d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                    />
                                </svg>
                                Laporan
                            </Link>

                            <Link
                                href="/pengaturan"
                                className="flex items-center justify-center px-4 py-2 text-sm text-gray-600 transition-colors duration-200 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
                            >
                                <svg
                                    className="w-4 h-4 mr-2"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth={2}
                                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"
                                    />
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth={2}
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                    />
                                </svg>
                                Pengaturan
                            </Link>
                        </div>
                    </div>

                    <div className="pt-6 mt-8 border-t border-gray-200">
                        <p className="text-xs text-gray-400">
                            Sistem Manajemen SDM GAPURA ANGKASA
                        </p>
                    </div>
                </div>
            </div>
        </>
    );
}
