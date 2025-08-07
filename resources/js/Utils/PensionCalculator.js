// Fungsi untuk menghitung TMT Pensiun berdasarkan tanggal lahir (56 tahun)
// File: resources/js/Utils/PensionCalculator.js

import { format, addYears } from "date-fns";

/**
 * Menghitung TMT Pensiun berdasarkan tanggal lahir (56 tahun)
 * @param {string} birthDate - Tanggal lahir dalam format YYYY-MM-DD
 * @returns {string} TMT Pensiun dalam format YYYY-MM-DD
 */
export const calculatePensionDate = (birthDate) => {
    if (!birthDate) return "";

    try {
        const birth = new Date(birthDate);
        const pensionDate = addYears(birth, 56);

        // Set ke tanggal 1 pada bulan yang sama
        pensionDate.setDate(1);

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
