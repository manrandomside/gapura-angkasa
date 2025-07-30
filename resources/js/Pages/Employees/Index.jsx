import React, { useState, useEffect } from 'react';
import DashboardLayout from '../../Layouts/DashboardLayout';

export default function EmployeesIndex() {
    const [employees, setEmployees] = useState([]);
    const [filteredEmployees, setFilteredEmployees] = useState([]);
    const [loading, setLoading] = useState(true);
    const [searchTerm, setSearchTerm] = useState('');
    const [filterStatus, setFilterStatus] = useState('all');
    const [currentPage, setCurrentPage] = useState(1);
    const [selectedEmployee, setSelectedEmployee] = useState(null);
    const [showDetailModal, setShowDetailModal] = useState(false);
    const [showCreateModal, setShowCreateModal] = useState(false);
    const [showEditModal, setShowEditModal] = useState(false);
    const [showDeleteModal, setShowDeleteModal] = useState(false);
    const itemsPerPage = 10;

    // Mock data - nanti akan diganti dengan API call
    useEffect(() => {
        // Simulate API call
        setTimeout(() => {
            const mockEmployees = [
                {
                    id: 1,
                    nip: '2160791',
                    nama_lengkap: 'I KETUT ADIYANA',
                    status_pegawai: 'PEGAWAI TETAP',
                    tmt_mulai_jabatan: '2023-02-15',
                    jenis_kelamin: 'L',
                    unit_organisasi: 'Back Office',
                    nama_jabatan: 'CONTROLLER HR & GA',
                    handphone: '+6281558924942',
                    email: 'ketut.adiyana@gapura.com'
                },
                {
                    id: 2,
                    nip: '2012140',
                    nama_lengkap: 'I NYOMAN JOHN SUPARTA',
                    status_pegawai: 'PEGAWAI TETAP',
                    tmt_mulai_jabatan: '2024-11-01',
                    jenis_kelamin: 'L',
                    unit_organisasi: 'Airside',
                    nama_jabatan: 'DEPARTURE CONTROL',
                    handphone: '+628123941122',
                    email: 'nyoman.suparta@gapura.com'
                },
                {
                    id: 3,
                    nip: '2201048',
                    nama_lengkap: 'A.A GEDE AGUNG WIRAJAYA',
                    status_pegawai: 'PEGAWAI TETAP',
                    tmt_mulai_jabatan: '2024-11-01',
                    jenis_kelamin: 'L',
                    unit_organisasi: 'Airside',
                    nama_jabatan: 'DEPARTURE CONTROL',
                    handphone: '+6281999234567',
                    email: 'agung.wirajaya@gapura.com'
                },
                {
                    id: 4,
                    nip: '2180234',
                    nama_lengkap: 'ABDUL RAHMAN HAKIM',
                    status_pegawai: 'TAD',
                    tmt_mulai_jabatan: '2018-03-01',
                    jenis_kelamin: 'L',
                    unit_organisasi: 'Avsec',
                    nama_jabatan: 'SECURITY OFFICER',
                    handphone: '+6281234567890',
                    email: 'abdul.rahman@gapura.com'
                },
                {
                    id: 5,
                    nip: '2190567',
                    nama_lengkap: 'DEWI SARTIKA PUTRI',
                    status_pegawai: 'PEGAWAI TETAP',
                    tmt_mulai_jabatan: '2019-06-15',
                    jenis_kelamin: 'P',
                    unit_organisasi: 'GSE',
                    nama_jabatan: 'EQUIPMENT OPERATOR',
                    handphone: '+6289876543210',
                    email: 'dewi.sartika@gapura.com'
                },
            ];
            setEmployees(mockEmployees);
            setFilteredEmployees(mockEmployees);
            setLoading(false);
        }, 1000);
    }, []);

    // Filter and search functionality
    useEffect(() => {
        let filtered = employees;

        if (searchTerm) {
            filtered = filtered.filter(employee => 
                employee.nama_lengkap.toLowerCase().includes(searchTerm.toLowerCase()) ||
                employee.nip.includes(searchTerm) ||
                employee.unit_organisasi.toLowerCase().includes(searchTerm.toLowerCase())
            );
        }

        if (filterStatus !== 'all') {
            filtered = filtered.filter(employee => employee.status_pegawai === filterStatus);
        }

        setFilteredEmployees(filtered);
        setCurrentPage(1);
    }, [searchTerm, filterStatus, employees]);

    // Pagination
    const totalPages = Math.ceil(filteredEmployees.length / itemsPerPage);
    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    const currentEmployees = filteredEmployees.slice(startIndex, endIndex);

    const formatDate = (dateString) => {
        if (!dateString) return '-';
        const date = new Date(dateString);
        return date.toLocaleDateString('id-ID', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
    };

    const handleViewDetail = (employee) => {
        setSelectedEmployee(employee);
        setShowDetailModal(true);
    };

    const handleEdit = (employee) => {
        setSelectedEmployee(employee);
        setShowEditModal(true);
    };

    const handleDelete = (employee) => {
        setSelectedEmployee(employee);
        setShowDeleteModal(true);
    };

    if (loading) {
        return (
            <DashboardLayout title="Management Karyawan">
                <div className="flex items-center justify-center min-h-screen">
                    <div className="flex items-center space-x-3">
                        <div className="w-8 h-8 border-4 border-[#439454] border-t-transparent rounded-full animate-spin"></div>
                        <span className="text-gray-600">Memuat data karyawan...</span>
                    </div>
                </div>
            </DashboardLayout>
        );
    }

    return (
        <DashboardLayout title="Management Karyawan">
            <div className="min-h-screen bg-gradient-to-br from-gray-50 via-white to-gray-50">
                {/* Header Section */}
                <div className="bg-white border-b border-gray-200 shadow-sm">
                    <div className="px-8 py-6">
                        <div className="flex items-center justify-between">
                            <div>
                                <h1 className="text-3xl font-bold text-gray-900">Management Karyawan</h1>
                                <p className="mt-2 text-gray-600">Kelola data karyawan GAPURA ANGKASA</p>
                            </div>
                            <button
                                onClick={() => setShowCreateModal(true)}
                                className="inline-flex items-center px-6 py-3 bg-[#439454] text-white font-semibold rounded-lg hover:bg-[#367a3f] transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
                            >
                                <svg className="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                Tambah Karyawan
                            </button>
                        </div>
                    </div>
                </div>

                {/* Filters and Search */}
                <div className="px-8 py-6">
                    <div className="p-6 bg-white border border-gray-200 shadow-sm rounded-xl">
                        <div className="flex flex-col space-y-4 md:flex-row md:items-center md:justify-between md:space-y-0">
                            <div className="flex flex-col space-y-3 sm:flex-row sm:items-center sm:space-y-0 sm:space-x-4">
                                <div className="relative">
                                    <input
                                        type="text"
                                        placeholder="Cari nama, NIP, atau unit..."
                                        value={searchTerm}
                                        onChange={(e) => setSearchTerm(e.target.value)}
                                        className="w-full sm:w-80 pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#439454] focus:border-transparent transition-all duration-200"
                                    />
                                    <svg className="absolute w-5 h-5 text-gray-400 left-3 top-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                                <select
                                    value={filterStatus}
                                    onChange={(e) => setFilterStatus(e.target.value)}
                                    className="px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#439454] focus:border-transparent transition-all duration-200"
                                >
                                    <option value="all">Semua Status</option>
                                    <option value="PEGAWAI TETAP">Pegawai Tetap</option>
                                    <option value="TAD">TAD</option>
                                </select>
                            </div>
                            <div className="text-sm text-gray-600">
                                Menampilkan {startIndex + 1}-{Math.min(endIndex, filteredEmployees.length)} dari {filteredEmployees.length} karyawan
                            </div>
                        </div>
                    </div>
                </div>

                {/* Table */}
                <div className="px-8 pb-8">
                    <div className="overflow-hidden bg-white border border-gray-200 shadow-sm rounded-xl">
                        <div className="overflow-x-auto">
                            <table className="w-full">
                                <thead className="border-b border-gray-200 bg-gray-50">
                                    <tr>
                                        <th className="px-6 py-4 text-xs font-semibold tracking-wider text-left text-gray-600 uppercase">NIP</th>
                                        <th className="px-6 py-4 text-xs font-semibold tracking-wider text-left text-gray-600 uppercase">Nama Lengkap</th>
                                        <th className="px-6 py-4 text-xs font-semibold tracking-wider text-left text-gray-600 uppercase">Status Pegawai</th>
                                        <th className="px-6 py-4 text-xs font-semibold tracking-wider text-left text-gray-600 uppercase">TMT Mulai Jabatan</th>
                                        <th className="px-6 py-4 text-xs font-semibold tracking-wider text-center text-gray-600 uppercase">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-200">
                                    {currentEmployees.map((employee, index) => (
                                        <tr key={employee.id} className="transition-colors duration-150 hover:bg-gray-50">
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <span className="font-mono text-sm text-gray-900">{employee.nip}</span>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <div className="flex items-center">
                                                    <div className="flex-shrink-0 w-10 h-10">
                                                        <div className="h-10 w-10 rounded-full bg-[#439454] flex items-center justify-center">
                                                            <span className="text-sm font-medium text-white">
                                                                {employee.nama_lengkap.split(' ').map(n => n[0]).join('').substring(0, 2)}
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div className="ml-4">
                                                        <div className="text-sm font-medium text-gray-900">{employee.nama_lengkap}</div>
                                                        <div className="text-sm text-gray-500">{employee.unit_organisasi}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <span className={`inline-flex px-3 py-1 text-xs font-semibold rounded-full ${
                                                    employee.status_pegawai === 'PEGAWAI TETAP' 
                                                        ? 'bg-green-100 text-green-800' 
                                                        : 'bg-yellow-100 text-yellow-800'
                                                }`}>
                                                    {employee.status_pegawai}
                                                </span>
                                            </td>
                                            <td className="px-6 py-4 text-sm text-gray-900 whitespace-nowrap">
                                                {formatDate(employee.tmt_mulai_jabatan)}
                                            </td>
                                            <td className="px-6 py-4 text-sm font-medium text-center whitespace-nowrap">
                                                <div className="flex items-center justify-center space-x-2">
                                                    <button
                                                        onClick={() => handleViewDetail(employee)}
                                                        className="p-2 text-blue-600 transition-all duration-200 rounded-lg hover:text-blue-800 hover:bg-blue-50"
                                                        title="Lihat Detail"
                                                    >
                                                        <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                        </svg>
                                                    </button>
                                                    <button
                                                        onClick={() => handleEdit(employee)}
                                                        className="text-[#439454] hover:text-[#367a3f] hover:bg-green-50 p-2 rounded-lg transition-all duration-200"
                                                        title="Edit"
                                                    >
                                                        <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                        </svg>
                                                    </button>
                                                    <button
                                                        onClick={() => handleDelete(employee)}
                                                        className="p-2 text-red-600 transition-all duration-200 rounded-lg hover:text-red-800 hover:bg-red-50"
                                                        title="Hapus"
                                                    >
                                                        <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>

                        {/* Pagination */}
                        {totalPages > 1 && (
                            <div className="px-6 py-3 bg-white border-t border-gray-200">
                                <div className="flex items-center justify-between">
                                    <div className="text-sm text-gray-700">
                                        Halaman {currentPage} dari {totalPages}
                                    </div>
                                    <div className="flex space-x-1">
                                        <button
                                            onClick={() => setCurrentPage(prev => Math.max(prev - 1, 1))}
                                            disabled={currentPage === 1}
                                            className="px-3 py-2 text-sm font-medium text-gray-500 transition-all duration-200 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                                        >
                                            Sebelumnya
                                        </button>
                                        {Array.from({ length: totalPages }, (_, i) => i + 1)
                                            .filter(page => page === 1 || page === totalPages || Math.abs(page - currentPage) <= 1)
                                            .map((page, index, array) => (
                                                <React.Fragment key={page}>
                                                    {index > 0 && array[index - 1] !== page - 1 && (
                                                        <span className="px-3 py-2 text-sm text-gray-500">...</span>
                                                    )}
                                                    <button
                                                        onClick={() => setCurrentPage(page)}
                                                        className={`px-3 py-2 text-sm font-medium rounded-md transition-all duration-200 ${
                                                            currentPage === page
                                                                ? 'bg-[#439454] text-white'
                                                                : 'text-gray-700 bg-white border border-gray-300 hover:bg-gray-50'
                                                        }`}
                                                    >
                                                        {page}
                                                    </button>
                                                </React.Fragment>
                                            ))}
                                        <button
                                            onClick={() => setCurrentPage(prev => Math.min(prev + 1, totalPages))}
                                            disabled={currentPage === totalPages}
                                            className="px-3 py-2 text-sm font-medium text-gray-500 transition-all duration-200 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                                        >
                                            Selanjutnya
                                        </button>
                                    </div>
                                </div>
                            </div>
                        )}
                    </div>
                </div>

                {/* Detail Modal */}
                {showDetailModal && selectedEmployee && (
                    <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-50">
                        <div className="bg-white rounded-xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
                            <div className="sticky top-0 flex items-center justify-between px-6 py-4 bg-white border-b border-gray-200">
                                <h2 className="text-2xl font-bold text-gray-900">Detail Karyawan</h2>
                                <button
                                    onClick={() => setShowDetailModal(false)}
                                    className="p-2 transition-colors duration-200 rounded-full hover:bg-gray-100"
                                >
                                    <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                            <div className="p-6">
                                <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                                    <div className="space-y-4">
                                        <div>
                                            <label className="block mb-1 text-sm font-medium text-gray-700">NIP</label>
                                            <p className="p-2 font-mono text-sm text-gray-900 rounded bg-gray-50">{selectedEmployee.nip}</p>
                                        </div>
                                        <div>
                                            <label className="block mb-1 text-sm font-medium text-gray-700">Nama Lengkap</label>
                                            <p className="p-2 text-sm text-gray-900 rounded bg-gray-50">{selectedEmployee.nama_lengkap}</p>
                                        </div>
                                        <div>
                                            <label className="block mb-1 text-sm font-medium text-gray-700">Status Pegawai</label>
                                            <p className="p-2 text-sm text-gray-900 rounded bg-gray-50">{selectedEmployee.status_pegawai}</p>
                                        </div>
                                        <div>
                                            <label className="block mb-1 text-sm font-medium text-gray-700">Unit Organisasi</label>
                                            <p className="p-2 text-sm text-gray-900 rounded bg-gray-50">{selectedEmployee.unit_organisasi}</p>
                                        </div>
                                        <div>
                                            <label className="block mb-1 text-sm font-medium text-gray-700">Nama Jabatan</label>
                                            <p className="p-2 text-sm text-gray-900 rounded bg-gray-50">{selectedEmployee.nama_jabatan}</p>
                                        </div>
                                    </div>
                                    <div className="space-y-4">
                                        <div>
                                            <label className="block mb-1 text-sm font-medium text-gray-700">TMT Mulai Jabatan</label>
                                            <p className="p-2 text-sm text-gray-900 rounded bg-gray-50">{formatDate(selectedEmployee.tmt_mulai_jabatan)}</p>
                                        </div>
                                        <div>
                                            <label className="block mb-1 text-sm font-medium text-gray-700">Jenis Kelamin</label>
                                            <p className="p-2 text-sm text-gray-900 rounded bg-gray-50">{selectedEmployee.jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan'}</p>
                                        </div>
                                        <div>
                                            <label className="block mb-1 text-sm font-medium text-gray-700">No. Handphone</label>
                                            <p className="p-2 text-sm text-gray-900 rounded bg-gray-50">{selectedEmployee.handphone}</p>
                                        </div>
                                        <div>
                                            <label className="block mb-1 text-sm font-medium text-gray-700">Email</label>
                                            <p className="p-2 text-sm text-gray-900 rounded bg-gray-50">{selectedEmployee.email}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div className="sticky bottom-0 flex justify-end px-6 py-4 bg-gray-50">
                                <button
                                    onClick={() => setShowDetailModal(false)}
                                    className="px-6 py-2 text-white transition-colors duration-200 bg-gray-600 rounded-lg hover:bg-gray-700"
                                >
                                    Tutup
                                </button>
                            </div>
                        </div>
                    </div>
                )}

                {/* Create Modal */}
                {showCreateModal && (
                    <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-50">
                        <div className="bg-white rounded-xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
                            <div className="sticky top-0 flex items-center justify-between px-6 py-4 bg-white border-b border-gray-200">
                                <h2 className="text-2xl font-bold text-gray-900">Tambah Karyawan Baru</h2>
                                <button
                                    onClick={() => setShowCreateModal(false)}
                                    className="p-2 transition-colors duration-200 rounded-full hover:bg-gray-100"
                                >
                                    <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                            <form className="p-6">
                                <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                                    <div className="space-y-4">
                                        <div>
                                            <label className="block mb-2 text-sm font-medium text-gray-700">NIP *</label>
                                            <input
                                                type="text"
                                                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#439454] focus:border-transparent transition-all duration-200"
                                            />
                                        </div>
                                        <div>
                                            <label className="block mb-2 text-sm font-medium text-gray-700">Jenis Kelamin *</label>
                                            <select className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#439454] focus:border-transparent transition-all duration-200">
                                                <option value="">Pilih Jenis Kelamin</option>
                                                <option value="L">Laki-laki</option>
                                                <option value="P">Perempuan</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label className="block mb-2 text-sm font-medium text-gray-700">No. Handphone</label>
                                            <input
                                                type="tel"
                                                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#439454] focus:border-transparent transition-all duration-200"
                                                placeholder="Masukkan nomor handphone"
                                            />
                                        </div>
                                        <div>
                                            <label className="block mb-2 text-sm font-medium text-gray-700">Email</label>
                                            <input
                                                type="email"
                                                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#439454] focus:border-transparent transition-all duration-200"
                                                placeholder="Masukkan email"
                                            />
                                        </div>
                                        <div>
                                            <label className="block mb-2 text-sm font-medium text-gray-700">Jenis Sepatu</label>
                                            <select className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#439454] focus:border-transparent transition-all duration-200">
                                                <option value="">Pilih Jenis Sepatu</option>
                                                <option value="Pantofel">Pantofel</option>
                                                <option value="Safety Shoes">Safety Shoes</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </form>
                            <div className="sticky bottom-0 flex justify-end px-6 py-4 space-x-3 bg-gray-50">
                                <button
                                    onClick={() => setShowCreateModal(false)}
                                    className="px-6 py-2 text-white transition-colors duration-200 bg-gray-600 rounded-lg hover:bg-gray-700"
                                >
                                    Batal
                                </button>
                                <button
                                    type="submit"
                                    className="px-6 py-2 bg-[#439454] text-white rounded-lg hover:bg-[#367a3f] transition-colors duration-200"
                                >
                                    Simpan
                                </button>
                            </div>
                        </div>
                    </div>
                )}

                {/* Edit Modal */}
                {showEditModal && selectedEmployee && (
                    <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-50">
                        <div className="bg-white rounded-xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
                            <div className="sticky top-0 flex items-center justify-between px-6 py-4 bg-white border-b border-gray-200">
                                <h2 className="text-2xl font-bold text-gray-900">Edit Karyawan</h2>
                                <button
                                    onClick={() => setShowEditModal(false)}
                                    className="p-2 transition-colors duration-200 rounded-full hover:bg-gray-100"
                                >
                                    <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                            <form className="p-6">
                                <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                                    <div className="space-y-4">
                                        <div>
                                            <label className="block mb-2 text-sm font-medium text-gray-700">NIP *</label>
                                            <input
                                                type="text"
                                                defaultValue={selectedEmployee.nip}
                                                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#439454] focus:border-transparent transition-all duration-200"
                                            />
                                        </div>
                                        <div>
                                            <label className="block mb-2 text-sm font-medium text-gray-700">Nama Lengkap *</label>
                                            <input
                                                type="text"
                                                defaultValue={selectedEmployee.nama_lengkap}
                                                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#439454] focus:border-transparent transition-all duration-200"
                                            />
                                        </div>
                                        <div>
                                            <label className="block mb-2 text-sm font-medium text-gray-700">Status Pegawai *</label>
                                            <select 
                                                defaultValue={selectedEmployee.status_pegawai}
                                                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#439454] focus:border-transparent transition-all duration-200"
                                            >
                                                <option value="PEGAWAI TETAP">Pegawai Tetap</option>
                                                <option value="TAD">TAD</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label className="block mb-2 text-sm font-medium text-gray-700">Unit Organisasi *</label>
                                            <select 
                                                defaultValue={selectedEmployee.unit_organisasi}
                                                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#439454] focus:border-transparent transition-all duration-200"
                                            >
                                                <option value="Back Office">Back Office</option>
                                                <option value="Airside">Airside</option>
                                                <option value="Landside">Landside</option>
                                                <option value="GSE">GSE</option>
                                                <option value="Avsec">Avsec</option>
                                                <option value="Ancillary">Ancillary</option>
                                                <option value="EGM">EGM</option>
                                                <option value="GM">GM</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label className="block mb-2 text-sm font-medium text-gray-700">Nama Jabatan *</label>
                                            <input
                                                type="text"
                                                defaultValue={selectedEmployee.nama_jabatan}
                                                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#439454] focus:border-transparent transition-all duration-200"
                                            />
                                        </div>
                                    </div>
                                    <div className="space-y-4">
                                        <div>
                                            <label className="block mb-2 text-sm font-medium text-gray-700">TMT Mulai Jabatan *</label>
                                            <input
                                                type="date"
                                                defaultValue={selectedEmployee.tmt_mulai_jabatan}
                                                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#439454] focus:border-transparent transition-all duration-200"
                                            />
                                        </div>
                                        <div>
                                            <label className="block mb-2 text-sm font-medium text-gray-700">Jenis Kelamin *</label>
                                            <select 
                                                defaultValue={selectedEmployee.jenis_kelamin}
                                                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#439454] focus:border-transparent transition-all duration-200"
                                            >
                                                <option value="L">Laki-laki</option>
                                                <option value="P">Perempuan</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label className="block mb-2 text-sm font-medium text-gray-700">No. Handphone</label>
                                            <input
                                                type="tel"
                                                defaultValue={selectedEmployee.handphone}
                                                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#439454] focus:border-transparent transition-all duration-200"
                                            />
                                        </div>
                                        <div>
                                            <label className="block mb-2 text-sm font-medium text-gray-700">Email</label>
                                            <input
                                                type="email"
                                                defaultValue={selectedEmployee.email}
                                                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#439454] focus:border-transparent transition-all duration-200"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </form>
                            <div className="sticky bottom-0 flex justify-end px-6 py-4 space-x-3 bg-gray-50">
                                <button
                                    onClick={() => setShowEditModal(false)}
                                    className="px-6 py-2 text-white transition-colors duration-200 bg-gray-600 rounded-lg hover:bg-gray-700"
                                >
                                    Batal
                                </button>
                                <button
                                    type="submit"
                                    className="px-6 py-2 bg-[#439454] text-white rounded-lg hover:bg-[#367a3f] transition-colors duration-200"
                                >
                                    Update
                                </button>
                            </div>
                        </div>
                    </div>
                )}

                {/* Delete Modal */}
                {showDeleteModal && selectedEmployee && (
                    <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-50">
                        <div className="w-full max-w-md bg-white shadow-2xl rounded-xl">
                            <div className="p-6">
                                <div className="flex items-center mb-4">
                                    <div className="flex items-center justify-center flex-shrink-0 w-10 h-10 mx-auto bg-red-100 rounded-full">
                                        <svg className="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                        </svg>
                                    </div>
                                </div>
                                <div className="text-center">
                                    <h3 className="mb-2 text-lg font-medium text-gray-900">Hapus Karyawan</h3>
                                    <p className="mb-4 text-sm text-gray-500">
                                        Apakah Anda yakin ingin menghapus karyawan <strong>{selectedEmployee.nama_lengkap}</strong>? 
                                        Tindakan ini tidak dapat dibatalkan.
                                    </p>
                                </div>
                            </div>
                            <div className="flex justify-end px-6 py-4 space-x-3 bg-gray-50 rounded-b-xl">
                                <button
                                    onClick={() => setShowDeleteModal(false)}
                                    className="px-4 py-2 text-white transition-colors duration-200 bg-gray-600 rounded-lg hover:bg-gray-700"
                                >
                                    Batal
                                </button>
                                <button
                                    onClick={() => {
                                        // Handle delete logic here
                                        setShowDeleteModal(false);
                                        setSelectedEmployee(null);
                                    }}
                                    className="px-4 py-2 text-white transition-colors duration-200 bg-red-600 rounded-lg hover:bg-red-700"
                                >
                                    Hapus
                                </button>
                            </div>
                        </div>
                    </div>
                )}
            </div>
        </DashboardLayout>
    );
}-transparent transition-all duration-200"
                                                placeholder="Masukkan NIP"
                                            />
                                        </div>
                                        <div>
                                            <label className="block mb-2 text-sm font-medium text-gray-700">Nama Lengkap *</label>
                                            <input
                                                type="text"
                                                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#439454] focus:border-transparent transition-all duration-200"
                                                placeholder="Masukkan nama lengkap"
                                            />
                                        </div>
                                        <div>
                                            <label className="block mb-2 text-sm font-medium text-gray-700">Status Pegawai *</label>
                                            <select className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#439454] focus:border-transparent transition-all duration-200">
                                                <option value="">Pilih Status</option>
                                                <option value="PEGAWAI TETAP">Pegawai Tetap</option>
                                                <option value="TAD">TAD</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label className="block mb-2 text-sm font-medium text-gray-700">Unit Organisasi *</label>
                                            <select className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#439454] focus:border-transparent transition-all duration-200">
                                                <option value="">Pilih Unit</option>
                                                <option value="Back Office">Back Office</option>
                                                <option value="Airside">Airside</option>
                                                <option value="Landside">Landside</option>
                                                <option value="GSE">GSE</option>
                                                <option value="Avsec">Avsec</option>
                                                <option value="Ancillary">Ancillary</option>
                                                <option value="EGM">EGM</option>
                                                <option value="GM">GM</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label className="block mb-2 text-sm font-medium text-gray-700">Nama Jabatan *</label>
                                            <input
                                                type="text"
                                                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#439454] focus:border-transparent transition-all duration-200"
                                                placeholder="Masukkan nama jabatan"
                                            />
                                        </div>
                                    </div>
                                    <div className="space-y-4">
                                        <div>
                                            <label className="block mb-2 text-sm font-medium text-gray-700">TMT Mulai Jabatan *</label>
                                            <input
                                                type="date"
                                                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#439454] focus:border