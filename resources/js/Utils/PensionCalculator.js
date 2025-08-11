// Fungsi untuk menghitung TMT Pensiun berdasarkan tanggal lahir (56 tahun dengan logika baru)
// File: resources/js/Utils/PensionCalculator.js

import { format, addYears, addMonths } from "date-fns";

/**
 * REVISI: Menghitung TMT Pensiun berdasarkan tanggal lahir (56 tahun dengan logika baru)
 * Logika baru:
 * - Jika lahir dibawah tanggal 10: pensiun 1 pada bulan yang sama
 * - Jika lahir diatas tanggal 10: pensiun 1 bulan berikutnya
 * @param {string} birthDate - Tanggal lahir dalam format YYYY-MM-DD
 * @returns {string} TMT Pensiun dalam format YYYY-MM-DD
 */
export const calculatePensionDate = (birthDate) => {
    if (!birthDate) return "";

    try {
        const birth = new Date(birthDate);

        // Tambah 56 tahun dari tanggal lahir
        const pensionDate = addYears(birth, 56);

        // REVISI: Logika TMT Pensiun berdasarkan aturan baru
        if (birth.getDate() < 10) {
            // Lahir dibawah tanggal 10: pensiun 1 pada bulan yang sama
            pensionDate.setDate(1);
        } else {
            // Lahir diatas tanggal 10: pensiun 1 bulan berikutnya
            pensionDate.setDate(1);
            pensionDate.setMonth(pensionDate.getMonth() + 1);
        }

        return format(pensionDate, "yyyy-MM-dd");
    } catch (error) {
        console.error("Error calculating pension date:", error);
        return "";
    }
};

/**
 * Menghitung umur berdasarkan tanggal lahir
 * @param {string} birthDate - Tanggal lahir dalam format YYYY-MM-DD
 * @returns {number} Umur dalam tahun
 */
export const calculateAge = (birthDate) => {
    if (!birthDate) return 0;

    try {
        const birth = new Date(birthDate);
        const today = new Date();
        let age = today.getFullYear() - birth.getFullYear();
        const monthDiff = today.getMonth() - birth.getMonth();

        if (
            monthDiff < 0 ||
            (monthDiff === 0 && today.getDate() < birth.getDate())
        ) {
            age--;
        }

        return age;
    } catch (error) {
        console.error("Error calculating age:", error);
        return 0;
    }
};

/**
 * Validasi tanggal lahir (tidak boleh di masa depan)
 * @param {string} birthDate - Tanggal lahir dalam format YYYY-MM-DD
 * @returns {boolean} True jika valid
 */
export const validateBirthDate = (birthDate) => {
    if (!birthDate) return false;

    try {
        const birth = new Date(birthDate);
        const today = new Date();

        return birth <= today;
    } catch (error) {
        return false;
    }
};

/**
 * BARU: Fungsi untuk menjelaskan logika perhitungan TMT Pensiun
 * @param {string} birthDate - Tanggal lahir dalam format YYYY-MM-DD
 * @returns {string} Penjelasan logika perhitungan
 */
export const getPensionCalculationExplanation = (birthDate) => {
    if (!birthDate) return "";

    try {
        const birth = new Date(birthDate);
        const dayOfBirth = birth.getDate();

        if (dayOfBirth < 10) {
            return `Lahir tanggal ${dayOfBirth} (dibawah tanggal 10), maka pensiun pada tanggal 1 di bulan yang sama saat berusia 56 tahun.`;
        } else {
            return `Lahir tanggal ${dayOfBirth} (diatas tanggal 10), maka pensiun pada tanggal 1 di bulan berikutnya saat berusia 56 tahun.`;
        }
    } catch (error) {
        return "Error menghitung penjelasan pension";
    }
};

/**
 * BARU: Mendapatkan informasi lengkap pension untuk display
 * @param {string} birthDate - Tanggal lahir dalam format YYYY-MM-DD
 * @returns {object} Object dengan pension date dan explanation
 */
export const getPensionInfo = (birthDate) => {
    return {
        pensionDate: calculatePensionDate(birthDate),
        explanation: getPensionCalculationExplanation(birthDate),
        age: calculateAge(birthDate),
        isValid: validateBirthDate(birthDate),
    };
};

/**
 * BARU: Fungsi untuk memvalidasi dan menghitung TMT Pensiun dengan detail
 * @param {string} birthDate - Tanggal lahir dalam format YYYY-MM-DD
 * @returns {object} Object dengan hasil perhitungan lengkap
 */
export const calculateDetailedPension = (birthDate) => {
    if (!birthDate) {
        return {
            success: false,
            error: "Tanggal lahir tidak boleh kosong",
            pensionDate: null,
            explanation: null,
            age: 0,
        };
    }

    try {
        const birth = new Date(birthDate);
        const today = new Date();

        // Validasi tanggal lahir
        if (birth > today) {
            return {
                success: false,
                error: "Tanggal lahir tidak boleh di masa depan",
                pensionDate: null,
                explanation: null,
                age: 0,
            };
        }

        const currentAge = calculateAge(birthDate);

        // Validasi usia minimal
        if (currentAge < 17) {
            return {
                success: false,
                error: "Usia minimal 17 tahun",
                pensionDate: null,
                explanation: null,
                age: currentAge,
            };
        }

        // Validasi usia maksimal
        if (currentAge > 70) {
            return {
                success: false,
                error: "Usia maksimal 70 tahun",
                pensionDate: null,
                explanation: null,
                age: currentAge,
            };
        }

        const pensionDate = calculatePensionDate(birthDate);
        const explanation = getPensionCalculationExplanation(birthDate);

        return {
            success: true,
            error: null,
            pensionDate: pensionDate,
            explanation: explanation,
            age: currentAge,
            dayOfBirth: birth.getDate(),
            pensionRule: birth.getDate() < 10 ? "same_month" : "next_month",
        };
    } catch (error) {
        return {
            success: false,
            error: "Terjadi kesalahan dalam perhitungan: " + error.message,
            pensionDate: null,
            explanation: null,
            age: 0,
        };
    }
};

/**
 * BARU: Format tanggal untuk display Indonesia
 * @param {string} dateString - Tanggal dalam format YYYY-MM-DD
 * @returns {string} Tanggal dalam format Indonesia
 */
export const formatDateIndonesia = (dateString) => {
    if (!dateString) return "";

    try {
        const date = new Date(dateString);
        const monthNames = [
            "Januari",
            "Februari",
            "Maret",
            "April",
            "Mei",
            "Juni",
            "Juli",
            "Agustus",
            "September",
            "Oktober",
            "November",
            "Desember",
        ];

        const day = date.getDate();
        const month = monthNames[date.getMonth()];
        const year = date.getFullYear();

        return `${day} ${month} ${year}`;
    } catch (error) {
        return dateString;
    }
};
