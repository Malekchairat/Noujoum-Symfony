# facial_recognition.py

import sys
import face_recognition

def main():
    if len(sys.argv) < 2:
        print("No image path provided.")
        sys.exit(1)

    image_path = sys.argv[1]
    try:
        image = face_recognition.load_image_file(image_path)
        face_locations = face_recognition.face_locations(image)

        if face_locations:
            print(f"Detected {len(face_locations)} face(s).")
        else:
            print("No faces detected.")
    except Exception as e:
        print(f"Error: {str(e)}")

if __name__ == "__main__":
    main()
