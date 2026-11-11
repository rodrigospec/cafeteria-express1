<?php

/** CRUD mediante procedimientos almacenados: CSRF, PDO, validación y mensajes. */
require_once __DIR__ . '/comun.php';
require_once __DIR__ . '/../config/conexion.php';
// Ejecuta un procedimiento almacenado y devuelve sus filas.
function sp(PDO $db, string $name, array $params = []): array
{
    if (!preg_match('/^sp_[a-z_]+_(listar|obtener|crear|actualizar|eliminar)$/', $name)) {
        throw new InvalidArgumentException('Procedimiento no permitido');
    }
    $q = $db->prepare('CALL ' . $name . '(' . implode(',', array_fill(0, count($params), '?')) . ')');
    $q->execute(array_values($params));
    $rows = $q->fetchAll();
    do {
    } while ($q->nextRowset());
    $q->closeCursor();
    return $rows;
}
// Reutiliza el mismo formulario y tabla para las cinco secciones.
function moduloCrud(string $tabla, string $titulo): void
{
    exigirAcceso();
    $schemas = require __DIR__ . '/esquemas.php';
    if (!isset($schemas[$tabla])) throw new RuntimeException('Módulo desconocido');
    $schema = $schemas[$tabla];
    $fields = $schema['fields'];
    $pk = $schema['pk'];
    $error = '';
    $ok = '';
    $db = null;
    $rows = [];
    $edit = null;
    $options = [];
    try {
        $db = conectarBD();
        foreach ($fields as $key => $def) if (isset($def['source'])) {
            $src = $def['source'];
            $options[$key] = sp($db, 'sp_' . $src . '_listar');
        }
        // POST crea, modifica o elimina; GET solamente consulta.
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verificarCsrf();
            $action = (string)($_POST['accion'] ?? '');
            $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
            if ($action === 'eliminar') {
                if (!$id || $id < 1) throw new RuntimeException('ID inválido');
                sp($db, 'sp_' . $tabla . '_eliminar', [$id]);
                $ok = 'Registro eliminado.';
            } elseif (in_array($action, ['crear', 'actualizar'], true)) {
                $params = [];
                foreach ($fields as $key => $def) {
                    $raw = trim((string)($_POST[$key] ?? ''));
                    if ($def['type'] === 'bool') $raw = isset($_POST[$key]) ? '1' : '0';
                    if ($raw === '' && !in_array($key, ['descripcion', 'motivo'], true)) throw new RuntimeException('Completa el campo ' . $key);
                    if ($def['type'] === 'int' && (!ctype_digit($raw) || (int)$raw < 0)) throw new RuntimeException('Número inválido: ' . $key);
                    if ($def['type'] === 'decimal' && (!is_numeric($raw) || (float)$raw < 0 || !preg_match('/^\d{1,8}(\.\d{1,2})?$/', $raw))) throw new RuntimeException('Importe inválido: ' . $key);
                    if (isset($def['options']) && !in_array($raw, $def['options'], true)) throw new RuntimeException('Opción inválida: ' . $key);
                    if (isset($def['source']) && (!ctype_digit($raw) || (int)$raw < 1)) throw new RuntimeException('Selecciona ' . $key);
                    if (in_array($key, ['cantidad'], true) && (int)$raw < 1) throw new RuntimeException('Cantidad debe ser mayor a cero.');
                    if (strlen($raw) > 255 && !in_array($key, ['descripcion'], true)) throw new RuntimeException('Texto demasiado largo: ' . $key);
                    $params[] = $raw === '' ? null : $raw;
                }
                if ($action === 'actualizar') {
                    if (!$id || $id < 1) throw new RuntimeException('ID inválido');
                    array_unshift($params, $id);
                }
                sp($db, 'sp_' . $tabla . '_' . $action, $params);
                $ok = 'Operación guardada correctamente.';
            } else throw new RuntimeException('Acción desconocida');
        }
        $editId = filter_input(INPUT_GET, 'editar', FILTER_VALIDATE_INT);
        if ($editId) {
            $found = sp($db, 'sp_' . $tabla . '_obtener', [$editId]);
            $edit = $found[0] ?? null;
        }
    } catch (Throwable $ex) {
        error_log('Cafetería Express: ' . $ex->getMessage());
        $error = 'No se pudo completar la operación. Revisa la conexión, los procedimientos y las relaciones de los registros.';
        if (getenv('APP_ENV') !== 'production') $error .= ' Detalle: ' . $ex->getMessage();
    }
    // Cargar la lista incluso si una operación anterior falló.
    if ($db instanceof PDO) {
        try {
            $rows = sp($db, 'sp_' . $tabla . '_listar');
            if ($_SERVER['REQUEST_METHOD'] === 'POST') $edit = null;
        } catch (Throwable $listEx) {
            error_log('Error al listar ' . $tabla . ': ' . $listEx->getMessage());
            $error .= ' No se pudo cargar la lista.';
        }
    }
    abrirPagina($titulo, $tabla);
    if ($error) avisoError($error);
    if ($ok) avisoOk($ok);
    if (!$db) {
        cerrarPagina();
        return;
    }
?>
    <section class="panel">
        <h2><?= $edit ? 'Editar registro' : 'Nuevo registro' ?></h2>
        <form method="post" class="form-grid">
            <input type="hidden" name="csrf" value="<?= e(csrf()) ?>">
            <input type="hidden" name="accion" value="<?= $edit ? 'actualizar' : 'crear' ?>">
            <?php if ($edit): ?><input type="hidden" name="id" value="<?= e($edit[$pk]) ?>"><?php endif; ?>
            <?php foreach ($fields as $name => $def): $value = $edit[$name] ?? ''; ?>
                <label class="field"><?= e($def['label']) ?>
                    <?php if ($def['type'] === 'select'): ?>
                        <select name="<?= e($name) ?>" required>
                            <option value="">Selecciona...</option>
                            <?php if (isset($def['source'])): foreach ($options[$name] ?? [] as $opt):
                                    $idKey = 'id_' . substr($def['source'], 0, -1);
                                    $idValue = $opt[$idKey] ?? ($opt['id_venta'] ?? '');
                                    $display = $opt['nombre'] ?? ('Venta #' . $idValue);
                            ?><option value="<?= e($idValue) ?>" <?= (string)$value === (string)$idValue ? 'selected' : '' ?>><?= e($display) ?></option><?php endforeach;
                                                                                                            else: foreach ($def['options'] as $option): ?><option value="<?= e($option) ?>" <?= (string)$value === (string)$option ? 'selected' : '' ?>><?= e($option) ?></option><?php endforeach;
                                                                                                                                                    endif; ?>
                        </select>
                    <?php elseif ($def['type'] === 'bool'): ?><input type="checkbox" name="<?= e($name) ?>" value="1" <?= (!$edit || $value) ? 'checked' : '' ?>>
                    <?php elseif ($name === 'descripcion' || $name === 'motivo'): ?><textarea name="<?= e($name) ?>" rows="2"><?= e($value) ?></textarea>
                    <?php else: ?><input name="<?= e($name) ?>" value="<?= e($value) ?>" type="<?= in_array($def['type'], ['int', 'decimal']) ? 'number' : 'text' ?>" <?= $def['type'] === 'decimal' ? 'step="0.01"' : 'step="1"' ?> <?= in_array($def['type'], ['int', 'decimal']) ? 'min="0"' : '' ?> required>
                    <?php endif; ?></label>
            <?php endforeach; ?><div class="actions"><button class="button" type="submit"><?= $edit ? 'Guardar cambios' : 'Registrar' ?></button><a class="button secondary" href="<?= e(basename($_SERVER['SCRIPT_NAME'])) ?>">Limpiar</a></div>
        </form>
    </section>
    <section class="panel">
        <h2>Registros existentes</h2>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>ID</th><?php foreach (array_keys($fields) as $key): ?><th><?= e(str_replace('_', ' ', $key)) ?></th><?php endforeach; ?><th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $row): ?><tr>
                            <td><?= e($row[$pk]) ?></td><?php foreach (array_keys($fields) as $key): ?><td><?= e($row[$key] ?? '') ?></td><?php endforeach; ?><td><a class="button secondary" href="?editar=<?= urlencode((string)$row[$pk]) ?>">Editar</a>
                                <form class="inline-form" method="post" onsubmit="return confirm('¿Eliminar este registro?')"><input type="hidden" name="csrf" value="<?= e(csrf()) ?>"><input type="hidden" name="accion" value="eliminar"><input type="hidden" name="id" value="<?= e($row[$pk]) ?>"><button class="button danger">Eliminar</button></form>
                            </td>
                        </tr><?php endforeach; ?>
                    <?php if (!$rows): ?><tr>
                            <td colspan="<?= count($fields) + 2 ?>">Sin registros.</td>
                        </tr><?php endif; ?></tbody>
            </table>
        </div>
    </section>
<?php cerrarPagina();
}
