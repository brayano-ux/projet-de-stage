import os

# signatures simples de virus (exemple)
virus_signatures = [
    "malware",
    "trojan",
    "virus"
]

def scan_file(file_path):
    try:
        with open(file_path, "r", errors="ignore") as file:
            content = file.read()
            for signature in virus_signatures:
                if signature in content.lower():
                    print("⚠ Virus détecté dans :", file_path)
                    return
    except:
        pass

def scan_folder(folder):
    for root, dirs, files in os.walk(folder):
        for file in files:
            path = os.path.join(root, file)
            scan_file(path)

# dossier à scanner
scan_folder("C:/Users")