package edu.rpi.pagr.misc;

/**
 * Created by Daniel Zhao on 10/10/13.
 */
public class AppetizerItem {

        private String name;
        private String description;
        private String price;
        private String imageURL;

        public String getImageURL() {
            return imageURL;
        }

        public void setImageURL(String url) {
            this.imageURL = url;
        }

        public String getName() {
            return name;
        }

        public void setName(String name) {
            this.name = name;
        }

        public String getDescription() {
            return description;
        }

        public void setDescription(String description) {
            this.description = description;
        }

        public String getPrice() {
            return price;
        }

        public void setPrice(String price) {
            this.price = price;
        }
}
