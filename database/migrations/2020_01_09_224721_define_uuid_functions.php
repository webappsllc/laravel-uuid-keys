<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DefineUuidFunctions extends Migration
{
    /**
     * Adds uuid <=> binary conversion functions that behave like the builtin versions in
     * MySQL 8. These functions DO NOT share the same names so that they will not collide in the
     * event of upgrading.
     *
     * Behavior is the same as UUID_TO_BIN( , true);
     *
     * @see - https://mohamedsharaf.net/uuid-uuid_to_bin-bin_to_uuid-mysql-8-ordered-by-default-bbackport-for-mysql-5-6-and-5-7-amazon-aws-compatible/
     * @see - http://mysql.rjweb.org/doc.php/uuid
     *
     * @return void
     */
    public function up()
    {
        $mysqlVersionCheck = DB::select(DB::raw('SHOW VARIABLES LIKE "version";'));
        $mysqlVersion = explode('.',$mysqlVersionCheck[0]->Value);

        $this->down();
        if((int)$mysqlVersion[0] < 8) {
            DB::unprepared(<<<SQL
                -- Turns a uuid string into a binary representation
                -- Behaves the same as UUID_TO_BIN( ,true)
                CREATE FUNCTION reorder_uuid2bin(_uuid BINARY(36))
                RETURNS BINARY(16)
                LANGUAGE SQL DETERMINISTIC CONTAINS SQL SQL SECURITY INVOKER
                RETURN
                UNHEX(CONCAT(
                    SUBSTR(_uuid, 15, 4),
                    SUBSTR(_uuid, 10, 4),
                    SUBSTR(_uuid, 1, 8),
                    SUBSTR(_uuid, 20, 4),
                    SUBSTR(_uuid, 25) ));
            SQL);

            DB::unprepared(<<<SQL
                -- Turns a 16 byte binary into a uuid string
                CREATE FUNCTION reorder_bin2uuid(_bin BINARY(16))
                RETURNS BINARY(36)
                LANGUAGE SQL DETERMINISTIC CONTAINS SQL SQL SECURITY INVOKER
                RETURN
                LCASE(CONCAT_WS('-',
                                HEX(SUBSTR(_bin, 5, 4)),
                                HEX(SUBSTR(_bin, 3, 2)),
                                HEX(SUBSTR(_bin, 1, 2)),
                                HEX(SUBSTR(_bin, 9, 2)),
                                HEX(SUBSTR(_bin, 11))
                ));
            SQL);
        } else {
            DB::unprepared(<<<SQL
                -- Turns a uuid string into a binary representation
                -- Behaves the same as UUID_TO_BIN( ,true)
                CREATE FUNCTION reorder_uuid2bin(_uuid BINARY(36))
                RETURNS BINARY(16)
                LANGUAGE SQL DETERMINISTIC CONTAINS SQL SQL SECURITY INVOKER
                RETURN UUID_TO_BIN(_uuid, 1);
            SQL);

            DB::unprepared(<<<SQL
                -- Turns a 16 byte binary into a uuid string
                CREATE FUNCTION reorder_bin2uuid(_bin BINARY(16))
                RETURNS BINARY(36)
                LANGUAGE SQL DETERMINISTIC CONTAINS SQL SQL SECURITY INVOKER
                RETURN BIN_TO_UUID(_bin, 1);
            SQL);
        }
        DB::unprepared(<<<SQL
            -- Turns a uuid string into a binary representation
            CREATE FUNCTION uuid2bin(_uuid BINARY(36))
            RETURNS BINARY(16)
            LANGUAGE SQL DETERMINISTIC CONTAINS SQL SQL SECURITY INVOKER
            RETURN UNHEX(REPLACE(_uuid, "-",""))
        SQL);

        DB::unprepared(<<<SQL
            -- Turns a 16 byte binary into a uuid string
            CREATE FUNCTION bin2uuid(_uuid BINARY(16))
            RETURNS BINARY(36)
            LANGUAGE SQL DETERMINISTIC CONTAINS SQL SQL SECURITY INVOKER
            BEGIN
                SET @hex_uuid = HEX(_uuid);
                RETURN LOWER(CONCAT_WS('-',
                    SUBSTR(@hex_uuid, 1, 8),
                    SUBSTR(@hex_uuid, 9, 4),
                    SUBSTR(@hex_uuid, 13, 4),
                    SUBSTR(@hex_uuid, 17, 4),
                    SUBSTR(@hex_uuid, 21)
                ));
            END
        SQL);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared("DROP FUNCTION IF EXISTS reorder_uuid2bin");
        DB::unprepared("DROP FUNCTION IF EXISTS reorder_bin2uuid");
        DB::unprepared("DROP FUNCTION IF EXISTS uuid2bin");
        DB::unprepared("DROP FUNCTION IF EXISTS bin2uuid");
    }
}
