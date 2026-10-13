<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * T-02 — Migración inicial
 *
 * Crea el esquema `library` y las seis tablas del data-model.md:
 * category, book, member, loan, loan_item, user.
 *
 * Incluye:
 * - FKs con ON DELETE RESTRICT / CASCADE según data-model.md §5
 * - Índices únicos y parciales de data-model.md §4
 * - CHECK ck_book_available_range (motor) — data-model.md §4
 * - Columna `version` en book para concurrencia optimista (ADR-002 / F-02)
 * - Semilla de las cinco categorías fijas (data-model.md §9)
 * - Sin DEFAULT en ninguna columna (data-model.md §3)
 * - Esquema library; timestamptz para todas las marcas de tiempo (RN-6)
 */
return new class extends Migration
{
    public function up(): void
    {
        // Crear el esquema library si no existe
        DB::statement('CREATE SCHEMA IF NOT EXISTS library');

        // Habilitar extensión uuid-ossp para gen_random_uuid()
        DB::statement('CREATE EXTENSION IF NOT EXISTS "pgcrypto"');

        // ---------------------------------------------------------------
        // category
        // ---------------------------------------------------------------
        DB::statement(<<<SQL
            CREATE TABLE library.category (
                id   uuid         NOT NULL,
                name varchar(120) NOT NULL,
                CONSTRAINT pk_category PRIMARY KEY (id),
                CONSTRAINT uq_category_name UNIQUE (name)
            )
        SQL);

        // ---------------------------------------------------------------
        // book
        // ---------------------------------------------------------------
        DB::statement(<<<SQL
            CREATE TABLE library.book (
                id               uuid          NOT NULL,
                title            varchar(250)  NOT NULL,
                author           varchar(200)  NOT NULL,
                isbn             char(13)      NOT NULL,
                category_id      uuid          NOT NULL,
                total_copies     integer       NOT NULL,
                available_copies integer       NOT NULL,
                cover_key        varchar(512)  NULL,
                deleted_at       timestamptz   NULL,
                version          integer       NOT NULL,
                CONSTRAINT pk_book PRIMARY KEY (id),
                CONSTRAINT fk_book_category
                    FOREIGN KEY (category_id) REFERENCES library.category (id)
                    ON DELETE RESTRICT ON UPDATE NO ACTION,
                CONSTRAINT ck_book_available_range
                    CHECK (available_copies >= 0 AND available_copies <= total_copies)
            )
        SQL);

        // ISBN único entre libros activos (índice parcial — data-model.md §4)
        DB::statement(
            'CREATE UNIQUE INDEX uq_book_isbn_active ON library.book (isbn) WHERE deleted_at IS NULL'
        );

        // Índice de acceso: búsqueda por categoría y título (solo activos)
        DB::statement(
            'CREATE INDEX ix_book_category_title ON library.book (category_id, title) WHERE deleted_at IS NULL'
        );

        // ---------------------------------------------------------------
        // member
        // ---------------------------------------------------------------
        DB::statement(<<<SQL
            CREATE TABLE library.member (
                id              uuid         NOT NULL,
                full_name       varchar(200) NOT NULL,
                document_number varchar(30)  NOT NULL,
                CONSTRAINT pk_member PRIMARY KEY (id),
                CONSTRAINT uq_member_document UNIQUE (document_number)
            )
        SQL);

        // ---------------------------------------------------------------
        // user  (entrecomillado porque es palabra reservada en PostgreSQL)
        // ---------------------------------------------------------------
        DB::statement(<<<SQL
            CREATE TABLE library."user" (
                id            uuid         NOT NULL,
                username      varchar(120) NOT NULL,
                password_hash varchar(512) NOT NULL,
                role          varchar(40)  NOT NULL,
                CONSTRAINT pk_user PRIMARY KEY (id),
                CONSTRAINT uq_user_username UNIQUE (username)
            )
        SQL);

        // ---------------------------------------------------------------
        // loan
        // ---------------------------------------------------------------
        DB::statement(<<<SQL
            CREATE TABLE library.loan (
                id                 uuid        NOT NULL,
                member_id          uuid        NOT NULL,
                loaned_by_user_id  uuid        NOT NULL,
                loaned_at          timestamptz NOT NULL,
                CONSTRAINT pk_loan PRIMARY KEY (id),
                CONSTRAINT fk_loan_member
                    FOREIGN KEY (member_id) REFERENCES library.member (id)
                    ON DELETE RESTRICT ON UPDATE NO ACTION,
                CONSTRAINT fk_loan_user
                    FOREIGN KEY (loaned_by_user_id) REFERENCES library."user" (id)
                    ON DELETE RESTRICT ON UPDATE NO ACTION
            )
        SQL);

        // Índice de acceso: filtro por fecha de préstamo
        DB::statement('CREATE INDEX ix_loan_loaned_at ON library.loan (loaned_at)');

        // ---------------------------------------------------------------
        // loan_item
        // ---------------------------------------------------------------
        DB::statement(<<<SQL
            CREATE TABLE library.loan_item (
                id            uuid         NOT NULL,
                loan_id       uuid         NOT NULL,
                book_id       uuid         NOT NULL,
                book_title    varchar(250) NOT NULL,
                isbn          char(13)     NOT NULL,
                category_name varchar(120) NOT NULL,
                due_at        timestamptz  NOT NULL,
                returned_at   timestamptz  NULL,
                CONSTRAINT pk_loan_item PRIMARY KEY (id),
                CONSTRAINT fk_loan_item_loan
                    FOREIGN KEY (loan_id) REFERENCES library.loan (id)
                    ON DELETE CASCADE ON UPDATE NO ACTION,
                CONSTRAINT fk_loan_item_book
                    FOREIGN KEY (book_id) REFERENCES library.book (id)
                    ON DELETE RESTRICT ON UPDATE NO ACTION,
                CONSTRAINT uq_loan_item_loan_book
                    UNIQUE (loan_id, book_id)
            )
        SQL);

        // Índices de acceso
        DB::statement('CREATE INDEX ix_loan_item_book_id ON library.loan_item (book_id)');
        DB::statement(
            'CREATE INDEX ix_loan_item_due_at_open ON library.loan_item (due_at) WHERE returned_at IS NULL'
        );

        // ---------------------------------------------------------------
        // Semilla: cinco categorías fijas (data-model.md §9)
        // ---------------------------------------------------------------
        DB::table('library.category')->insert([
            ['id' => '11111111-1111-4111-8111-111111111111', 'name' => 'Novela'],
            ['id' => '22222222-2222-4222-8222-222222222222', 'name' => 'Ciencia'],
            ['id' => '33333333-3333-4333-8333-333333333333', 'name' => 'Tecnología'],
            ['id' => '44444444-4444-4444-8444-444444444444', 'name' => 'Historia'],
            ['id' => '55555555-5555-4555-8555-555555555555', 'name' => 'Infantil'],
        ]);
    }

    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS library.loan_item');
        DB::statement('DROP TABLE IF EXISTS library.loan');
        DB::statement('DROP TABLE IF EXISTS library."user"');
        DB::statement('DROP TABLE IF EXISTS library.member');
        DB::statement('DROP TABLE IF EXISTS library.book');
        DB::statement('DROP TABLE IF EXISTS library.category');
        DB::statement('DROP SCHEMA IF EXISTS library');
    }
};
