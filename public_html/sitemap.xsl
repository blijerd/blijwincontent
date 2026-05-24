<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:sm="http://www.sitemaps.org/schemas/sitemap/0.9">
    <xsl:output method="html" encoding="UTF-8" indent="yes"/>

    <xsl:template match="/">
        <html lang="nl">
            <head>
                <meta charset="UTF-8"/>
                <meta name="viewport" content="width=device-width, initial-scale=1"/>
                <title>Sitemap - Blijwin Content CMS</title>
                <style>
                    :root {
                        color-scheme: light;
                        --bg: #f8fafc;
                        --panel: #ffffff;
                        --text: #172033;
                        --muted: #607086;
                        --line: #d8e0ea;
                        --accent: #0f766e;
                    }

                    * {
                        box-sizing: border-box;
                    }

                    body {
                        margin: 0;
                        background: var(--bg);
                        color: var(--text);
                        font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
                        line-height: 1.5;
                    }

                    main {
                        width: min(1120px, calc(100% - 32px));
                        margin: 0 auto;
                        padding: 48px 0;
                    }

                    header {
                        margin-bottom: 28px;
                    }

                    h1 {
                        margin: 0 0 8px;
                        font-size: 32px;
                        font-weight: 700;
                    }

                    p {
                        margin: 0;
                        color: var(--muted);
                    }

                    table {
                        width: 100%;
                        border-collapse: collapse;
                        overflow: hidden;
                        background: var(--panel);
                        border: 1px solid var(--line);
                        border-radius: 8px;
                    }

                    th,
                    td {
                        padding: 14px 16px;
                        text-align: left;
                        border-bottom: 1px solid var(--line);
                        vertical-align: top;
                    }

                    th {
                        color: var(--muted);
                        font-size: 13px;
                        font-weight: 700;
                        text-transform: uppercase;
                    }

                    tr:last-child td {
                        border-bottom: 0;
                    }

                    a {
                        color: var(--accent);
                        font-weight: 650;
                        overflow-wrap: anywhere;
                        text-decoration: none;
                    }

                    a:hover {
                        text-decoration: underline;
                    }

                    .date {
                        color: var(--muted);
                        white-space: nowrap;
                    }

                    @media (max-width: 720px) {
                        main {
                            width: min(100% - 24px, 1120px);
                            padding: 28px 0;
                        }

                        h1 {
                            font-size: 26px;
                        }

                        table,
                        thead,
                        tbody,
                        tr,
                        th,
                        td {
                            display: block;
                        }

                        thead {
                            display: none;
                        }

                        tr {
                            border-bottom: 1px solid var(--line);
                        }

                        tr:last-child {
                            border-bottom: 0;
                        }

                        td {
                            border-bottom: 0;
                            padding: 12px 14px;
                        }

                        td + td {
                            padding-top: 0;
                        }

                        .date {
                            white-space: normal;
                        }
                    }
                </style>
            </head>
            <body>
                <main>
                    <header>
                        <h1>Sitemap</h1>
                        <p>
                            <xsl:value-of select="count(sm:urlset/sm:url)"/>
                            <xsl:text> gepubliceerde pagina's</xsl:text>
                        </p>
                    </header>

                    <table>
                        <thead>
                            <tr>
                                <th>URL</th>
                                <th>Laatst gewijzigd</th>
                            </tr>
                        </thead>
                        <tbody>
                            <xsl:for-each select="sm:urlset/sm:url">
                                <tr>
                                    <td>
                                        <a>
                                            <xsl:attribute name="href">
                                                <xsl:value-of select="sm:loc"/>
                                            </xsl:attribute>
                                            <xsl:value-of select="sm:loc"/>
                                        </a>
                                    </td>
                                    <td class="date">
                                        <xsl:value-of select="sm:lastmod"/>
                                    </td>
                                </tr>
                            </xsl:for-each>
                        </tbody>
                    </table>
                </main>
            </body>
        </html>
    </xsl:template>
</xsl:stylesheet>
