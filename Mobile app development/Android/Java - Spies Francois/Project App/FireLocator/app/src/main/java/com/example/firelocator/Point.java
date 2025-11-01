package com.example.firelocator; // Make sure this matches your package name

// Based on code from
public class Point {
    double lat, lon, alt;
    static double R = 6378137; // radius earth in meter [cite: 22]

    Point(double x, double y) {
        // This was a syntax error in the PDF [cite: 23, 25]
        this.lat = x;
        this.lon = y;
        this.alt = 0;
    }

    // Distance between to GPS coordinates [cite: 26]
    double distance(Point b) {
        double dLon = (b.lon - this.lon) / 180 * Math.PI;
        double l1 = this.lat / 180 * Math.PI;
        // Typo "b b.lat" corrected from [cite: 32]
        double l2 = b.lat / 180 * Math.PI;
        double alpha = Math.sin(l1) * Math.sin(l2) + Math.cos(l1) * Math.cos(l2)
                * Math.cos(dLon);
        double c = Math.acos(alpha);
        return R * c;
    }

    // Azimut/direction of point b from point a [cite: 38]
    double azimut(Point b) {
        double dLon = (b.lon - this.lon) / 180 * Math.PI;
        double l1 = this.lat / 180 * Math.PI;
        double l2 = b.lat / 180 * Math.PI;
        double x = Math.sin(dLon) * Math.cos(l2);
        double y = Math.cos(l1) * Math.sin(l2)
                - Math.sin(l1) * Math.cos(l2) *
                Math.cos(dLon);
        // Missing "=" corrected from [cite: 46]
        double aziRad = Math.atan2(y, x);
        return 180 * aziRad / Math.PI;
    }

    // Calculus of the destination coordinates [cite: 49]
    Point destination(double d, double azi) {
        // "dest" must be initialized [cite: 52]
        Point dest = new Point(0, 0);

        double l1 = this.lat / 180 * Math.PI;
        double aziRad = azi / 180 * Math.PI;
        // Missing ")" corrected from [cite: 56]
        double destLat = Math.asin(Math.sin(l1) * Math.cos(d / R) +
                Math.cos(l1) * Math.sin(d / R) * Math.cos(aziRad));

        // Syntax error corrected from [cite: 57]
        double y = Math.sin(aziRad) * Math.sin(d / R) * Math.cos(l1);
        double x = Math.cos(d / R) - Math.sin(l1) * Math.sin(destLat);

        dest.lat = 180 * destLat / Math.PI;
        // Critical bug corrected: was "this.lat" in PDF [cite: 58]
        dest.lon = this.lon + 180 * Math.atan2(y, x) / Math.PI;
        return dest;
    }
}