<?php
// Configuración de la base de datos
$servidor = "158.69.26.160";
$usuario = "admin";
$contrasena = "F@c3b00k";
$BD = "whanum";

try {
    // Conexión a la base de datos
    $conexion = new PDO("mysql:host=$servidor;dbname=$BD;charset=utf8", $usuario, $contrasena);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Exportar enlaces a CSV
    if (isset($_GET['export_csv']) && isset($_GET['municipio'])) {
        $municipioSeleccionado = $_GET['municipio'];

        // Consulta para obtener los enlaces
        $queryExport = "
            WITH municipios AS (
                SELECT 'Othón P. Blanco (Chetumal)' AS municipio, '983' AS lada
                UNION ALL SELECT 'Felipe Carrillo Puerto', '983'
                UNION ALL SELECT 'Bacalar', '983'
                UNION ALL SELECT 'Benito Juárez (Cancún)', '998'
                UNION ALL SELECT 'Isla Mujeres', '998'
                UNION ALL SELECT 'Puerto Morelos', '998'
                UNION ALL SELECT 'Solidaridad (Playa del Carmen)', '984'
                UNION ALL SELECT 'Tulum', '984'
                UNION ALL SELECT 'Lázaro Cárdenas', '984'
                UNION ALL SELECT 'Cozumel', '987'
                UNION ALL SELECT 'José María Morelos', '997'
            )
            SELECT 
                n.Link AS enlace_grupo
            FROM 
                municipios m
            LEFT JOIN 
                whanum.whatsapp_group_members n ON 
                    SUBSTRING(n.numero_telefono, 7, 3) = m.lada 
                    AND n.numero_telefono LIKE '+52%' 
            WHERE 
                m.municipio = :municipio
            GROUP BY 
                n.Link
            HAVING n.Link IS NOT NULL;
        ";

        // Preparar y ejecutar la consulta
        $stmtExport = $conexion->prepare($queryExport);
        $stmtExport->bindParam(':municipio', $municipioSeleccionado);
        $stmtExport->execute();
        $enlaces = $stmtExport->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener el municipio seleccionado
    $municipioSeleccionado = isset($_GET['municipio']) ? $_GET['municipio'] : '';

    // Consulta SQL para mostrar los enlaces en pantalla
    $query = "
        WITH municipios AS (
            SELECT 'Othón P. Blanco (Chetumal)' AS municipio, '983' AS lada
            UNION ALL SELECT 'Felipe Carrillo Puerto', '983'
            UNION ALL SELECT 'Bacalar', '983'
            UNION ALL SELECT 'Benito Juárez (Cancún)', '998'
            UNION ALL SELECT 'Isla Mujeres', '998'
            UNION ALL SELECT 'Puerto Morelos', '998'
            UNION ALL SELECT 'Solidaridad (Playa del Carmen)', '984'
            UNION ALL SELECT 'Tulum', '984'
            UNION ALL SELECT 'Lázaro Cárdenas', '984'
            UNION ALL SELECT 'Cozumel', '987'
            UNION ALL SELECT 'José María Morelos', '997'
        )
        SELECT 
            m.municipio,
            n.titulo AS nombre_grupo,
            n.Link AS enlace_grupo
        FROM 
            municipios m
        LEFT JOIN 
            whanum.whatsapp_group_members n ON 
                SUBSTRING(n.numero_telefono, 7, 3) = m.lada 
                AND n.numero_telefono LIKE '+52%' 
        WHERE 
            m.municipio = :municipio
        GROUP BY 
            m.municipio, 
            n.titulo, 
            n.Link
        ORDER BY 
            m.municipio ASC;
    ";

    // Preparar y ejecutar la consulta
    $stmt = $conexion->prepare($query);
    $stmt->bindParam(':municipio', $municipioSeleccionado);
    $stmt->execute();

    // Obtener resultados
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Lógica para exportar a CSV
    if (isset($_GET['export_csv']) && $_GET['export_csv'] === 'true') {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . urlencode($municipioSeleccionado) . '_enlaces.csv"');

        foreach ($resultados as $fila) {
            echo '"' . htmlspecialchars($fila['enlace_grupo']) . "\"\n"; // Solo se exportan los enlaces
        }
        exit; // Termina la ejecución después de enviar el CSV
    }

    // Generar tabla HTML
    if ($resultados) {
        // Botón para exportar enlaces a CSV
        echo '<a href="quintanaroo.php?municipio=' . urlencode($municipioSeleccionado) . '&export_csv=true" class="export-button">Exportar Enlaces a CSV</a>';
        echo '<br><br>'; // Añade espacio entre el botón y la tabla

        // Comienza la tabla
        echo '<table>';
        echo '<thead><tr><th>Municipio</th><th>Nombre del Grupo</th><th>Enlace del Grupo</th></tr></thead>'; // Incluye el nombre del grupo
        echo '<tbody>';
        foreach ($resultados as $fila) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($fila['municipio']) . '</td>';
            echo '<td>' . htmlspecialchars($fila['nombre_grupo']) . '</td>'; // Agregado el nombre del grupo
            echo '<td><a href="' . htmlspecialchars($fila['enlace_grupo']) . '" target="_blank">' . htmlspecialchars($fila['enlace_grupo']) . '</a></td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
    } else {
        echo 'No se encontraron grupos para el municipio seleccionado.';
    }

} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>
