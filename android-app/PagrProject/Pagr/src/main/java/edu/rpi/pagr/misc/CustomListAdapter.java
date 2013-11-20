package edu.rpi.pagr.misc;

import java.util.ArrayList;
import android.content.Context;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.BaseAdapter;
import android.widget.ImageView;
import android.widget.TextView;

import edu.rpi.pagr.R;
import edu.rpi.pagr.misc.AppetizerItem;
import edu.rpi.pagr.task.ImageDownloaderTask;
/*
Not in Use
 */

public class CustomListAdapter extends BaseAdapter {

    private ArrayList listData;

    private LayoutInflater layoutInflater;

    public CustomListAdapter(Context context, ArrayList listData) {
        this.listData = listData;
        layoutInflater = LayoutInflater.from(context);
    }

    @Override
    public int getCount() {
        return listData.size();
    }

    @Override
    public Object getItem(int position) {
        return listData.get(position);
    }

    @Override
    public long getItemId(int position) {
        return position;
    }

    public View getView(int position, View convertView, ViewGroup parent) {
        ViewHolder holder;
        if (convertView == null) {
            convertView = layoutInflater.inflate(R.layout.list_appetizer_item, null);
            holder = new ViewHolder();
            holder.titleView = (TextView) convertView.findViewById(R.id.appetizer_title);
            holder.imageView = (ImageView) convertView.findViewById(R.id.thumbImage);
            convertView.setTag(holder);
        } else {
            holder = (ViewHolder) convertView.getTag();
        }

        AppetizerItem appetizerItem = (AppetizerItem) listData.get(position);

        holder.titleView.setText(appetizerItem.getName());

        if (holder.imageView != null) {
            new ImageDownloaderTask(holder.imageView).execute(appetizerItem.getImageURL());
        }

        return convertView;
    }

    static class ViewHolder {
        TextView titleView;
        ImageView imageView;
    }
}
