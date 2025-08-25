// Extended characteristics for avatar-compact.js
// This file contains the updated animal, fantasy, and robot templates with expanded options

const extendedCharacteristicTemplates = {
    animal: [
        {
            field: 'species',
            label: 'Espécie',
            type: 'select',
            options: [
                { value: 'domestic cat', label: 'Gato Doméstico' },
                { value: 'big cat', label: 'Felino Grande (Leão, Tigre)' },
                { value: 'domestic dog', label: 'Cachorro Doméstico' },
                { value: 'wolf', label: 'Lobo' },
                { value: 'horse', label: 'Cavalo' },
                { value: 'unicorn', label: 'Unicórnio' },
                { value: 'bird of prey', label: 'Ave de Rapina (Águia, Falcão)' },
                { value: 'songbird', label: 'Pássaro Canoro' },
                { value: 'exotic bird', label: 'Ave Exótica (Papagaio, Tucano)' },
                { value: 'bear', label: 'Urso' },
                { value: 'deer', label: 'Cervo' },
                { value: 'rabbit', label: 'Coelho' },
                { value: 'fox', label: 'Raposa' },
                { value: 'dragon', label: 'Dragão' }
            ]
        },
        {
            field: 'fur_pattern',
            label: 'Padrão da Pelagem',
            type: 'multi-select',
            options: [
                { value: 'solid color', label: 'Cor Sólida' },
                { value: 'striped', label: 'Listrado' },
                { value: 'spotted', label: 'Manchado' },
                { value: 'tabby', label: 'Malhado' },
                { value: 'calico', label: 'Tricolor' },
                { value: 'gradient', label: 'Degradê' },
                { value: 'patchy', label: 'Remendado' }
            ]
        },
        {
            field: 'fur_color',
            label: 'Cor da Pelagem',
            type: 'multi-select',
            options: [
                { value: 'white fur', label: 'Pelagem Branca' },
                { value: 'black fur', label: 'Pelagem Preta' },
                { value: 'brown fur', label: 'Pelagem Marrom' },
                { value: 'golden fur', label: 'Pelagem Dourada' },
                { value: 'gray fur', label: 'Pelagem Cinza' },
                { value: 'orange fur', label: 'Pelagem Laranja' },
                { value: 'cream fur', label: 'Pelagem Creme' },
                { value: 'silver fur', label: 'Pelagem Prateada' }
            ]
        },
        {
            field: 'eye_color_animal',
            label: 'Cor dos Olhos',
            type: 'select',
            options: [
                { value: 'amber eyes', label: 'Olhos Âmbar' },
                { value: 'green eyes', label: 'Olhos Verdes' },
                { value: 'blue eyes', label: 'Olhos Azuis' },
                { value: 'brown eyes', label: 'Olhos Castanhos' },
                { value: 'golden eyes', label: 'Olhos Dourados' },
                { value: 'heterochromia', label: 'Heterocromia (cores diferentes)' }
            ]
        },
        {
            field: 'size',
            label: 'Tamanho',
            type: 'select',
            options: [
                { value: 'tiny', label: 'Minúsculo' },
                { value: 'small', label: 'Pequeno' },
                { value: 'medium', label: 'Médio' },
                { value: 'large', label: 'Grande' },
                { value: 'huge', label: 'Enorme' },
                { value: 'giant', label: 'Gigante' },
                { value: 'colossal', label: 'Colossal' }
            ]
        },
        {
            field: 'special_features',
            label: 'Características Especiais',
            type: 'multi-select',
            options: [
                { value: 'fluffy', label: 'Fofinho' },
                { value: 'majestic mane', label: 'Juba Majestosa' },
                { value: 'long tail', label: 'Cauda Longa' },
                { value: 'bushy tail', label: 'Cauda Peluda' },
                { value: 'pointed ears', label: 'Orelhas Pontudas' },
                { value: 'floppy ears', label: 'Orelhas Caídas' },
                { value: 'antlers', label: 'Chifres' },
                { value: 'wings', label: 'Asas' },
                { value: 'scales', label: 'Escamas' }
            ]
        }
    ],
    fantasy: [
        {
            field: 'creature_type',
            label: 'Tipo de Criatura',
            type: 'select',
            options: [
                { value: 'high elf', label: 'Alto Elfo' },
                { value: 'wood elf', label: 'Elfo da Floresta' },
                { value: 'dark elf', label: 'Elfo Sombrio' },
                { value: 'mountain dwarf', label: 'Anão da Montanha' },
                { value: 'forest fairy', label: 'Fada da Floresta' },
                { value: 'water nymph', label: 'Ninfa da Água' },
                { value: 'fire dragon', label: 'Dragão de Fogo' },
                { value: 'ice dragon', label: 'Dragão de Gelo' },
                { value: 'phoenix', label: 'Fênix' },
                { value: 'unicorn', label: 'Unicórnio' },
                { value: 'centaur', label: 'Centauro' },
                { value: 'angel', label: 'Anjo' },
                { value: 'demon', label: 'Demônio' },
                { value: 'witch', label: 'Bruxa' },
                { value: 'wizard', label: 'Mago' }
            ]
        },
        {
            field: 'magical_abilities',
            label: 'Habilidades Mágicas',
            type: 'multi-select',
            options: [
                { value: 'fire magic', label: 'Magia do Fogo' },
                { value: 'ice magic', label: 'Magia do Gelo' },
                { value: 'water magic', label: 'Magia da Água' },
                { value: 'earth magic', label: 'Magia da Terra' },
                { value: 'air magic', label: 'Magia do Ar' },
                { value: 'nature magic', label: 'Magia da Natureza' },
                { value: 'healing magic', label: 'Magia de Cura' },
                { value: 'shadow magic', label: 'Magia das Sombras' },
                { value: 'light magic', label: 'Magia da Luz' },
                { value: 'time magic', label: 'Magia do Tempo' },
                { value: 'teleportation', label: 'Teletransporte' },
                { value: 'shapeshifting', label: 'Metamorfose' }
            ]
        },
        {
            field: 'fantasy_appearance',
            label: 'Aparência Fantástica',
            type: 'multi-select',
            options: [
                { value: 'pointed ears', label: 'Orelhas Pontudas' },
                { value: 'glowing eyes', label: 'Olhos Brilhantes' },
                { value: 'ethereal glow', label: 'Brilho Etéreo' },
                { value: 'crystal skin', label: 'Pele Cristalina' },
                { value: 'iridescent scales', label: 'Escamas Iridescentes' },
                { value: 'feathered wings', label: 'Asas Emplumadas' },
                { value: 'bat wings', label: 'Asas de Morcego' },
                { value: 'horns', label: 'Chifres' },
                { value: 'tail', label: 'Cauda' },
                { value: 'markings', label: 'Marcas Mágicas' }
            ]
        },
        {
            field: 'magical_artifacts',
            label: 'Artefatos Mágicos',
            type: 'multi-select',
            options: [
                { value: 'magical staff', label: 'Cajado Mágico' },
                { value: 'enchanted sword', label: 'Espada Encantada' },
                { value: 'spell book', label: 'Livro de Magias' },
                { value: 'crystal orb', label: 'Orbe de Cristal' },
                { value: 'magical amulet', label: 'Amuleto Mágico' },
                { value: 'enchanted jewelry', label: 'Joias Encantadas' },
                { value: 'magic wand', label: 'Varinha Mágica' },
                { value: 'potion bottles', label: 'Poções' }
            ]
        }
    ],
    robot: [
        {
            field: 'robot_type',
            label: 'Tipo de Robô',
            type: 'select',
            options: [
                { value: 'humanoid android', label: 'Android Humanoide' },
                { value: 'battle android', label: 'Android de Combate' },
                { value: 'service android', label: 'Android de Serviço' },
                { value: 'cyborg human', label: 'Cyborg Humano' },
                { value: 'full cyborg', label: 'Cyborg Completo' },
                { value: 'combat mech', label: 'Mech de Combate' },
                { value: 'utility mech', label: 'Mech Utilitário' },
                { value: 'AI hologram', label: 'Holograma IA' },
                { value: 'drone robot', label: 'Robô Drone' }
            ]
        },
        {
            field: 'materials',
            label: 'Materiais',
            type: 'multi-select',
            options: [
                { value: 'chrome plating', label: 'Revestimento Cromado' },
                { value: 'matte black metal', label: 'Metal Preto Fosco' },
                { value: 'brushed steel', label: 'Aço Escovado' },
                { value: 'carbon fiber', label: 'Fibra de Carbono' },
                { value: 'titanium alloy', label: 'Liga de Titânio' },
                { value: 'synthetic skin', label: 'Pele Sintética' },
                { value: 'transparent panels', label: 'Painéis Transparentes' },
                { value: 'glowing circuits', label: 'Circuitos Luminosos' }
            ]
        },
        {
            field: 'tech_features',
            label: 'Recursos Tecnológicos',
            type: 'multi-select',
            options: [
                { value: 'LED eyes', label: 'Olhos LED' },
                { value: 'holographic display', label: 'Display Holográfico' },
                { value: 'energy core', label: 'Núcleo de Energia' },
                { value: 'mechanical joints', label: 'Juntas Mecânicas' },
                { value: 'retractable weapons', label: 'Armas Retráteis' },
                { value: 'scanner arrays', label: 'Arrays de Scanner' },
                { value: 'antenna', label: 'Antenas' },
                { value: 'jetpack', label: 'Propulsores' }
            ]
        },
        {
            field: 'design_style',
            label: 'Estilo de Design',
            type: 'select',
            options: [
                { value: 'sleek futuristic', label: 'Futurista Elegante' },
                { value: 'industrial heavy', label: 'Industrial Pesado' },
                { value: 'minimalist clean', label: 'Minimalista Limpo' },
                { value: 'steampunk mechanical', label: 'Steampunk Mecânico' },
                { value: 'bio-mechanical hybrid', label: 'Híbrido Bio-mecânico' },
                { value: 'retro sci-fi', label: 'Sci-fi Retrô' },
                { value: 'alien technology', label: 'Tecnologia Alienígena' }
            ]
        }
    ]
};

// Function to merge these templates into the main avatar-compact.js file
function mergeExtendedTemplates() {
    if (window.avatarCompact && window.avatarCompact.characteristicTemplates) {
        // Merge extended templates
        Object.assign(window.avatarCompact.characteristicTemplates, extendedCharacteristicTemplates);
        console.log('Extended avatar characteristics loaded successfully!');
    }
}

// Auto-merge when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    setTimeout(mergeExtendedTemplates, 1000); // Wait for main avatar system to load
});