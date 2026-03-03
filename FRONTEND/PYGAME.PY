import pygame
import random
import sys

# Initialisation de Pygame
pygame.init()
# Constantes
LARGEUR = 800
HAUTEUR = 600
GRILLE_LARGEUR = 10
GRILLE_HAUTEUR = 20
TAILLE_BLOC = 30
GRILLE_X = (LARGEUR - GRILLE_LARGEUR * TAILLE_BLOC) // 2
GRILLE_Y = 50
# Couleurs
NOIR = (0, 0, 0)
BLANC = (255, 255, 255)
ROUGE = (255, 0, 0)
VERT = (0, 255, 0)
BLEU = (0, 0, 255)
JAUNE = (255, 255, 0)
ORANGE = (255, 165, 0)
VIOLET = (128, 0, 128)
CYAN = (0, 255, 255)
GRIS = (128, 128, 128)

# Formes des pièces Tetris
PIECES = [
    # I-piece
    [['.....',
      '..#..',
      '..#..',
      '..#..',
      '..#..'],
     ['.....',
      '.....',
      '####.',
      '.....',
      '.....']],
    
    # O-piece
    [['.....',
      '.....',
      '.##..',
      '.##..',
      '.....']],
    
    # T-piece
    [['.....',
      '.....',
      '.#...',
      '###..',
      '.....'],
     ['.....',
      '.....',
      '.#...',
      '.##..',
      '.#...'],
     ['.....',
      '.....',
      '.....',
      '###..',
      '.#...'],
     ['.....',
      '.....',
      '.#...',
      '##...',
      '.#...']],
    
    # S-piece
    [['.....',
      '.....',
      '.##..',
      '##...',
      '.....'],
     ['.....',
      '.#...',
      '.##..',
      '..#..',
      '.....']],
    
    # Z-piece
    [['.....',
      '.....',
      '##...',
      '.##..',
      '.....'],
     ['.....',
      '..#..',
      '.##..',
      '.#...',
      '.....']],
    
    # J-piece
    [['.....',
      '.#...',
      '.#...',
      '##...',
      '.....'],
     ['.....',
      '.....',
      '#....',
      '###..',
      '.....'],
     ['.....',
      '.##..',
      '.#...',
      '.#...',
      '.....'],
     ['.....',
      '.....',
      '###..',
      '..#..',
      '.....']],
    
    # L-piece
    [['.....',
      '..#..',
      '..#..',
      '.##..',
      '.....'],
     ['.....',
      '.....',
      '###..',
      '#....',
      '.....'],
     ['.....',
      '##...',
      '.#...',
      '.#...',
      '.....'],
     ['.....',
      '.....',
      '..#..',
      '###..',
      '.....']]
]

COULEURS_PIECES = [CYAN, JAUNE, VIOLET, VERT, ROUGE, BLEU, ORANGE]

class Piece:
    def __init__(self, x, y):
        self.x = x
        self.y = y
        self.type = random.randint(0, len(PIECES) - 1)
        self.rotation = 0
        self.couleur = COULEURS_PIECES[self.type]
    def image(self):
        return PIECES[self.type][self.rotation]
    
    def rotate(self):
        self.rotation = (self.rotation + 1) % len(PIECES[self.type])

class Tetris:
    def __init__(self):
        self.grille = [[NOIR for _ in range(GRILLE_LARGEUR)] for _ in range(GRILLE_HAUTEUR)]
        self.piece_actuelle = self.nouvelle_piece()
        self.prochaine_piece = self.nouvelle_piece()
        self.score = 0
        self.lignes = 0
        self.niveau = 1
        self.temps_chute = 500  # millisecondes
        self.derniere_chute = pygame.time.get_ticks()
        self.jeu_fini = False
    
    def nouvelle_piece(self):
        return Piece(GRILLE_LARGEUR // 2 - 2, 0)
    
    def piece_valide(self, piece, dx=0, dy=0, rotation=None):
        if rotation is None:
            rotation = piece.rotation
        
        for y, ligne in enumerate(PIECES[piece.type][rotation]):
            for x, cellule in enumerate(ligne):
                if cellule == '#':
                    nx = piece.x + x + dx
                    ny = piece.y + y + dy
                    
                    if (nx < 0 or nx >= GRILLE_LARGEUR or 
                        ny >= GRILLE_HAUTEUR or 
                        (ny >= 0 and self.grille[ny][nx] != NOIR)):
                        return False
        return True
    
    def placer_piece(self):
        for y, ligne in enumerate(self.piece_actuelle.image()):
            for x, cellule in enumerate(ligne):
                if cellule == '#':
                    nx = self.piece_actuelle.x + x
                    ny = self.piece_actuelle.y + y
                    if ny >= 0:
                        self.grille[ny][nx] = self.piece_actuelle.couleur
        
        self.effacer_lignes()
        self.piece_actuelle = self.prochaine_piece
        self.prochaine_piece = self.nouvelle_piece()
        
        if not self.piece_valide(self.piece_actuelle):
            self.jeu_fini = True
    
    def effacer_lignes(self):
        lignes_effacees = 0
        y = GRILLE_HAUTEUR - 1
        
        while y >= 0:
            if all(cellule != NOIR for cellule in self.grille[y]):
                del self.grille[y]
                self.grille.insert(0, [NOIR for _ in range(GRILLE_LARGEUR)])
                lignes_effacees += 1
            else:
                y -= 1
        
        if lignes_effacees > 0:
            self.lignes += lignes_effacees
            self.score += lignes_effacees * 100 * self.niveau
            self.niveau = self.lignes // 10 + 1
            self.temps_chute = max(50, 500 - (self.niveau - 1) * 50)
    
    def faire_tomber(self):
        if self.piece_valide(self.piece_actuelle, dy=1):
            self.piece_actuelle.y += 1
        else:
            self.placer_piece()
    
    def deplacer(self, dx):
        if self.piece_valide(self.piece_actuelle, dx=dx):
            self.piece_actuelle.x += dx
    
    def tourner(self):
        nouvelle_rotation = (self.piece_actuelle.rotation + 1) % len(PIECES[self.piece_actuelle.type])
        if self.piece_valide(self.piece_actuelle, rotation=nouvelle_rotation):
            self.piece_actuelle.rotation = nouvelle_rotation
    
    def chute_rapide(self):
        while self.piece_valide(self.piece_actuelle, dy=1):
            self.piece_actuelle.y += 1
        self.placer_piece()

def dessiner_bloc(surface, x, y, couleur):
    rect = pygame.Rect(GRILLE_X + x * TAILLE_BLOC, GRILLE_Y + y * TAILLE_BLOC, 
                      TAILLE_BLOC, TAILLE_BLOC)
    pygame.draw.rect(surface, couleur, rect)
    pygame.draw.rect(surface, BLANC, rect, 2)

def dessiner_grille(surface, jeu):
    # Dessiner la grille
    for y in range(GRILLE_HAUTEUR):
        for x in range(GRILLE_LARGEUR):
            if jeu.grille[y][x] != NOIR:
                dessiner_bloc(surface, x, y, jeu.grille[y][x])
    
    # Dessiner la pièce actuelle
    if not jeu.jeu_fini:
        for y, ligne in enumerate(jeu.piece_actuelle.image()):
            for x, cellule in enumerate(ligne):
                if cellule == '#':
                    dessiner_bloc(surface, jeu.piece_actuelle.x + x, 
                                jeu.piece_actuelle.y + y, jeu.piece_actuelle.couleur)
    
    # Dessiner les bordures
    pygame.draw.rect(surface, BLANC, 
                    (GRILLE_X - 2, GRILLE_Y - 2, 
                     GRILLE_LARGEUR * TAILLE_BLOC + 4, 
                     GRILLE_HAUTEUR * TAILLE_BLOC + 4), 2)

def dessiner_texte(surface, texte, x, y, taille=24, couleur=BLANC):
    font = pygame.font.Font(None, taille)
    text_surface = font.render(texte, True, couleur)
    surface.blit(text_surface, (x, y))

def main():
    screen = pygame.display.set_mode((LARGEUR, HAUTEUR))
    pygame.display.set_caption("Tetris")
    clock = pygame.time.Clock()
    
    jeu = Tetris()
    
    while True:
        temps_actuel = pygame.time.get_ticks()
        
        for event in pygame.event.get():
            if event.type == pygame.QUIT:
                pygame.quit()
                sys.exit()
            
            if event.type == pygame.KEYDOWN and not jeu.jeu_fini:
                if event.key == pygame.K_LEFT:
                    jeu.deplacer(-1)
                elif event.key == pygame.K_RIGHT:
                    jeu.deplacer(1)
                elif event.key == pygame.K_DOWN:
                    jeu.faire_tomber()
                elif event.key == pygame.K_UP:
                    jeu.tourner()
                elif event.key == pygame.K_SPACE:
                    jeu.chute_rapide()
            
            if event.type == pygame.KEYDOWN and jeu.jeu_fini:
                if event.key == pygame.K_r:
                    jeu = Tetris()
        
        # Chute automatique des pièces
        if not jeu.jeu_fini and temps_actuel - jeu.derniere_chute > jeu.temps_chute:
            jeu.faire_tomber()
            jeu.derniere_chute = temps_actuel
        
        # Dessiner
        screen.fill(NOIR)
        dessiner_grille(screen, jeu)
        
        # Afficher les informations
        dessiner_texte(screen, f"Score: {jeu.score}", 50, 100)
        dessiner_texte(screen, f"Lignes: {jeu.lignes}", 50, 130)
        dessiner_texte(screen, f"Niveau: {jeu.niveau}", 50, 160)
        
        # Instructions
        dessiner_texte(screen, "Flèches: Déplacer", 50, 300, 18)
        dessiner_texte(screen, "↑: Tourner", 50, 320, 18)
        dessiner_texte(screen, "Espace: Chute rapide", 50, 340, 18)
        
        if jeu.jeu_fini:
            dessiner_texte(screen, "GAME OVER", LARGEUR//2 - 80, HAUTEUR//2, 36, ROUGE)
            dessiner_texte(screen, "Appuyez sur R pour recommencer", LARGEUR//2 - 140, HAUTEUR//2 + 40, 18)
        
        pygame.display.flip()
        clock.tick(60)

if __name__ == "__main__":
    main()