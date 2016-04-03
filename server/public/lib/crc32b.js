/**
 * http://stackoverflow.com/questions/18638900/javascript-crc32 より
 */

class CRC32B {
    static const() {
        CRC32B.table = new Uint32Array(256);

        // Pre-generate crc32 polynomial lookup table
        // http://wiki.osdev.org/CRC32#Building_the_Lookup_Table
        // ... Actually use Alex's because it generates the correct bit order
        //     so no need for the reversal function
        for (var i = 256; i--;) {
            var tmp = i;

            for (var k = 8; k--;) {
                tmp = tmp & 1 ? 3988292384 ^ tmp >>> 1 : tmp >>> 1;
            }

            CRC32B.table[i] = tmp;
        }
    }

    /**
     * 渡された文字列データのCRC32B値を求めて返す
     * @param string data 求める文字列
     * @return string CRC32B
     */
    static crc32b(data) {
        var crc = -1; // Begin with all bits set ( 0xffffffff )

        // テーブルが作成
        if (CRC32B.table == null) {
            CRC32B.const();
        }

        for (var i = 0, l = data.length; i < l; i++) {
            crc = crc >>> 8 ^ CRC32B.table[crc & 255 ^ data[i]];
        }

        return (crc ^ -1) >>> 0; // Apply binary NOT
    }
}
