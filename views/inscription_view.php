<?php
declare(strict_types=1);
require_once __DIR__ . '/layout/header.php';

// Détermination automatique de la saison
$moisActuel = (int)date('n');
$anneeActuelle = (int)date('Y');
$saisonActuelle = ($moisActuel >= 8) ? $anneeActuelle . '-' . ($anneeActuelle + 1) : ($anneeActuelle - 1) . '-' . $anneeActuelle;
?>

<div class="max-w-4xl mx-auto px-4 py-8 sm:px-6 lg:px-8">
    <div class="bg-white rounded-lg shadow-md border-t-4 border-blue-700 overflow-hidden">
        
        <div class="px-6 py-6 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Fiche d'Inscription</h1>
                <p class="mt-1 text-sm text-gray-600">Saison sportive <?= htmlspecialchars($saisonActuelle, ENT_QUOTES, 'UTF-8') ?></p>
            </div>
            <img src="/public/images/logo-panda.png" alt="Logo Judo" class="h-16 w-16 object-contain hidden sm:block" onerror="this.style.display='none'">
        </div>

        <form id="form-inscription" class="px-6 py-8 space-y-8">
            <input type="hidden" name="saison" value="<?= htmlspecialchars($saisonActuelle, ENT_QUOTES, 'UTF-8') ?>">

            <!-- SECTION 1 : ÉTAT CIVIL -->
            <fieldset class="border border-gray-200 rounded-md p-4 bg-white">
                <legend class="text-lg font-bold text-blue-800 px-2">Informations Adhérent(e)</legend>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 mt-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nom *</label>
                        <input type="text" name="nom" required class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md py-2 px-3 border">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Prénom *</label>
                        <input type="text" name="prenom" required class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md py-2 px-3 border">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Sexe *</label>
                        <select name="sexe" required class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm sm:text-sm">
                            <option value="">Sélectionner</option>
                            <option value="Masculin">Masculin</option>
                            <option value="Féminin">Féminin</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Date de naissance</label>
                        <input type="date" name="date_naissance" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md py-2 px-3 border">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Adresse postale</label>
                        <input type="text" name="adresse" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md py-2 px-3 border">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">E-mail</label>
                        <input type="email" name="email" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md py-2 px-3 border">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Téléphone de l'adhérent</label>
                        <input type="tel" name="telephone" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md py-2 px-3 border">
                    </div>
                </div>
            </fieldset>

            <!-- SECTION 2 : PRATIQUE SPORTIVE -->
            <fieldset class="border border-gray-200 rounded-md p-4 bg-gray-50">
                <legend class="text-lg font-bold text-blue-800 px-2">Discipline(s) & Niveau</legend>
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 mt-2">
                    <div>
                        <span class="block text-sm font-medium text-gray-700 mb-2">Discipline(s) choisie(s) *</span>
                        <!-- Retrait de la discipline Ju-jitsu -->
                        <div class="flex space-x-4">
                            <label class="inline-flex items-center"><input type="checkbox" name="disciplines[]" value="Judo" class="rounded text-blue-600 focus:ring-blue-500 border-gray-300"><span class="ml-2 text-sm text-gray-700">Judo</span></label>
                            <label class="inline-flex items-center"><input type="checkbox" name="disciplines[]" value="MMA" class="rounded text-blue-600 focus:ring-blue-500 border-gray-300"><span class="ml-2 text-sm text-gray-700">MMA</span></label>
                            <label class="inline-flex items-center"><input type="checkbox" name="disciplines[]" value="TFT" class="rounded text-blue-600 focus:ring-blue-500 border-gray-300"><span class="ml-2 text-sm text-gray-700">TFT</span></label>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Couleur de ceinture</label>
                        <input type="text" name="ceinture" placeholder="Ex: Blanche, Jaune..." class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md py-2 px-3 border">
                    </div>
                </div>
            </fieldset>

            <!-- SECTION 3 : CONTACTS & PROFESSIONS -->
            <fieldset class="border border-gray-200 rounded-md p-4 bg-white">
                <legend class="text-lg font-bold text-blue-800 px-2">Famille & Professions</legend>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 mt-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Téléphone Père (si mineur)</label>
                        <input type="tel" name="telephone_pere" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md py-2 px-3 border">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Téléphone Mère (si mineur)</label>
                        <input type="tel" name="telephone_mere" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md py-2 px-3 border">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Profession Père</label>
                        <input type="text" name="profession_pere" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md py-2 px-3 border">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Profession Mère</label>
                        <input type="text" name="profession_mere" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md py-2 px-3 border">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Profession Adulte (si adhérent majeur)</label>
                        <input type="text" name="profession_adulte" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md py-2 px-3 border">
                    </div>
                </div>
            </fieldset>

            <!-- SECTION 4 : SANTÉ & URGENCE -->
            <fieldset class="border border-gray-200 rounded-md p-4 bg-red-50 border-red-200">
                <legend class="text-lg font-bold text-red-700 px-2">Santé & Urgence</legend>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 mt-2">
                    <div class="sm:col-span-3">
                        <label class="block text-sm font-medium text-gray-700">Problèmes de santé à signaler (allergies, asthme...)</label>
                        <input type="text" name="problemes_sante" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md py-2 px-3 border">
                    </div>
                    <div class="sm:col-span-3 text-sm font-bold text-gray-700 mt-2">Personne à contacter en cas d'urgence :</div>
                    <div>
                        <label class="block text-sm text-gray-600">Nom</label>
                        <input type="text" name="urgence_nom" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md py-2 px-3 border">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600">Prénom</label>
                        <input type="text" name="urgence_prenom" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md py-2 px-3 border">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600">Téléphone</label>
                        <input type="tel" name="urgence_telephone" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md py-2 px-3 border">
                    </div>
                </div>
            </fieldset>

            <!-- SECTION 5 : MÉDICAL & AUTORISATIONS -->
            <fieldset class="border border-gray-200 rounded-md p-4 bg-white">
                <legend class="text-lg font-bold text-blue-800 px-2">Médical & Autorisations</legend>
                <div class="space-y-4 mt-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Document médical fourni *</label>
                        <select name="type_certificat" required class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm sm:text-sm">
                            <option value="">Sélectionner</option>
                            <option value="Certificat médical">Certificat médical</option>
                            <option value="Attestation QS Mineur">Attestation QS Sport (Mineur)</option>
                            <option value="Attestation QS Majeur">Attestation QS Sport (Majeur)</option>
                        </select>
                    </div>
                    <div class="flex items-start">
                        <div class="flex items-center h-5"><input type="checkbox" name="autorisation_soins" value="1" required class="h-4 w-4 text-blue-600 border-gray-300 rounded"></div>
                        <div class="ml-3 text-sm"><label class="font-medium text-gray-700">Autorisation de soins (Obligatoire) *</label><p class="text-gray-500">J'autorise les responsables à prendre toutes dispositions en cas d'accident.</p></div>
                    </div>
                    <div class="flex items-start">
                        <div class="flex items-center h-5"><input type="checkbox" name="droit_image" value="1" class="h-4 w-4 text-blue-600 border-gray-300 rounded"></div>
                        <div class="ml-3 text-sm"><label class="font-medium text-gray-700">Droit à l'image</label><p class="text-gray-500">J'autorise le club à diffuser des photographies prises dans le cadre de ses activités.</p></div>
                    </div>
                </div>
            </fieldset>

            <!-- SECTION 6 : PAIEMENT (Cadre Club) -->
            <fieldset class="border-2 border-green-500 rounded-md p-4 bg-green-50">
                <legend class="text-lg font-bold text-green-700 px-2">Tarification & Paiement</legend>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 mt-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Montant Total (€)</label>
                        <input type="number" step="0.01" name="montant_total" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md py-2 px-3 border" placeholder="205.00">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Mode de règlement</label>
                        <select name="mode_paiement" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm sm:text-sm">
                            <option value="">Sélectionner</option>
                            <option value="Virement">Virement (comptant)</option>
                            <option value="Espèce">Espèce (comptant)</option>
                            <option value="Chèques">Chèques</option>
                            <option value="Chèques vacances">Chèques vacances</option>
                            <option value="Pass Sport">Pass'sport</option>
                            <option value="Coupon sport">Coupon sport</option>
                        </select>
                    </div>
                    <div class="sm:col-span-2 flex items-center space-x-6">
                        <label class="inline-flex items-center"><input type="checkbox" name="reduction_appliquee" value="1" class="rounded text-green-600 focus:ring-green-500 border-gray-300"><span class="ml-2 text-sm text-gray-700 text-green-800 font-medium">Réduction -10€ appliquée</span></label>
                    </div>
                </div>
            </fieldset>

            <div class="pt-4 flex justify-end">
                <button type="submit" id="btn-submit-inscription" class="bg-blue-700 border border-transparent rounded-md shadow-sm py-3 px-8 inline-flex justify-center text-base font-bold text-white hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 btn-transition disabled:opacity-50">
                    Enregistrer l'inscription
                </button>
            </div>
        </form>
    </div>
</div>

</main>
<script src="/public/js/toast.js"></script>
<script src="/public/js/inscription.js"></script>
</body>
</html>