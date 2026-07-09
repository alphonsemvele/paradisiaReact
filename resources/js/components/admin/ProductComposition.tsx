import { Box, Layers, Plus, Trash2 } from 'lucide-react';

export interface ComponentRow {
    id_component_product: number | '';
    quantity: number;
}

/** Sélecteur du type de produit : simple ou composé. */
export function TypeSelector({
    value,
    onChange,
}: {
    value: 'simple' | 'compose';
    onChange: (v: 'simple' | 'compose') => void;
}) {
    return (
        <div>
            <label className="block text-sm font-medium text-zinc-700 mb-1.5">Type de produit *</label>
            <div className="grid grid-cols-2 gap-3">
                <button
                    type="button"
                    onClick={() => onChange('simple')}
                    className={`flex items-center gap-2 py-3 px-3 rounded-xl border-2 text-sm font-medium transition-all ${
                        value === 'simple'
                            ? 'border-emerald-500 bg-emerald-50 text-emerald-700'
                            : 'border-zinc-200 bg-zinc-50 text-zinc-600 hover:border-zinc-300'
                    }`}
                >
                    <Box className="w-5 h-5" />
                    Simple
                </button>
                <button
                    type="button"
                    onClick={() => onChange('compose')}
                    className={`flex items-center gap-2 py-3 px-3 rounded-xl border-2 text-sm font-medium transition-all ${
                        value === 'compose'
                            ? 'border-orange-500 bg-orange-50 text-orange-700'
                            : 'border-zinc-200 bg-zinc-50 text-zinc-600 hover:border-zinc-300'
                    }`}
                >
                    <Layers className="w-5 h-5" />
                    Composé
                </button>
            </div>
        </div>
    );
}

/** Constructeur de composition d'un produit composé : lignes (produit simple + quantité). */
export function CompositionBuilder({
    value,
    onChange,
    simpleProducts,
    errors,
}: {
    value: ComponentRow[];
    onChange: (rows: ComponentRow[]) => void;
    simpleProducts: Array<{ id: number; name: string }>;
    errors: Record<string, string>;
}) {
    const addRow = () => onChange([...value, { id_component_product: '', quantity: 1 }]);
    const removeRow = (i: number) => onChange(value.filter((_, idx) => idx !== i));
    const updateRow = (i: number, patch: Partial<ComponentRow>) =>
        onChange(value.map((row, idx) => (idx === i ? { ...row, ...patch } : row)));

    return (
        <div className="rounded-xl border-2 border-orange-100 bg-orange-50/40 p-4">
            <div className="flex items-center justify-between mb-1">
                <h3 className="text-sm font-semibold text-zinc-800 flex items-center gap-2">
                    <Layers className="w-4 h-4 text-orange-500" />
                    Composition
                </h3>
                <button
                    type="button"
                    onClick={addRow}
                    className="inline-flex items-center gap-1 text-xs font-medium text-orange-700 hover:bg-orange-100 px-2 py-1 rounded-lg"
                >
                    <Plus className="w-3.5 h-3.5" /> Ajouter un composant
                </button>
            </div>
            <p className="text-xs text-zinc-500 mb-3">
                Choisissez les produits simples et la quantité contenue (ex : Bouteille × 12).
            </p>

            {errors.components && <p className="text-xs text-red-600 mb-2">{errors.components}</p>}

            {value.length === 0 && (
                <p className="text-xs text-zinc-400 italic py-2">
                    Aucun composant. Cliquez sur « Ajouter un composant ».
                </p>
            )}

            <div className="space-y-2">
                {value.map((row, i) => (
                    <div key={i} className="flex items-center gap-2">
                        <select
                            value={row.id_component_product}
                            onChange={(e) =>
                                updateRow(i, {
                                    id_component_product: e.target.value ? Number(e.target.value) : '',
                                })
                            }
                            className="flex-1 px-3 py-2 bg-white border border-zinc-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-400"
                        >
                            <option value="">— Produit simple —</option>
                            {simpleProducts.map((p) => (
                                <option key={p.id} value={p.id}>
                                    {p.name}
                                </option>
                            ))}
                        </select>
                        <input
                            type="number"
                            min={1}
                            value={row.quantity}
                            onChange={(e) => updateRow(i, { quantity: Math.max(1, Number(e.target.value) || 1) })}
                            className="w-20 px-3 py-2 bg-white border border-zinc-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-400"
                        />
                        <button
                            type="button"
                            onClick={() => removeRow(i)}
                            className="p-2 text-zinc-500 hover:text-red-600 hover:bg-red-50 rounded-lg"
                            title="Retirer"
                        >
                            <Trash2 className="w-4 h-4" />
                        </button>
                    </div>
                ))}
            </div>

            {simpleProducts.length === 0 && (
                <p className="text-xs text-amber-600 mt-2">
                    Créez d'abord des produits <strong>simples</strong> pour pouvoir composer.
                </p>
            )}
        </div>
    );
}
