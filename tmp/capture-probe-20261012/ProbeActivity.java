package th.eimaths.captureprobe;

import android.app.Activity;
import android.content.SharedPreferences;
import android.graphics.Canvas;
import android.graphics.Color;
import android.graphics.Paint;
import android.os.Build;
import android.os.Bundle;
import android.util.Log;
import android.view.SurfaceHolder;
import android.view.SurfaceView;
import android.view.WindowManager;
import android.widget.Button;
import android.widget.LinearLayout;
import android.widget.ScrollView;
import android.widget.TextView;
import java.text.SimpleDateFormat;
import java.util.Date;
import java.util.Locale;
import java.util.function.Consumer;

/** Standalone synthetic-content probe. Never connects to eiMaths or loads real books. */
public final class ProbeActivity extends Activity {
    private SharedPreferences prefs;
    private boolean windowMode;
    private TextView status;
    private TextView history;
    private Activity.ScreenCaptureCallback screenshotCallback;
    private Consumer<Integer> recordingCallback;
    private int screenshots;
    private int recordingStarts;
    private boolean recording;

    @Override public void onCreate(Bundle savedState) {
        super.onCreate(savedState);
        prefs = getSharedPreferences("probe", MODE_PRIVATE);
        windowMode = prefs.getBoolean("windowMode", true);
        screenshots = prefs.getInt("screenshots", 0);
        recordingStarts = prefs.getInt("recordingStarts", 0);
        if (windowMode) getWindow().addFlags(WindowManager.LayoutParams.FLAG_SECURE);

        LinearLayout root = new LinearLayout(this);
        root.setOrientation(LinearLayout.VERTICAL);
        root.setPadding(20, 20, 20, 20);
        root.setBackgroundColor(Color.WHITE);
        root.setOnApplyWindowInsetsListener((view, insets) -> {
            android.graphics.Insets safe = insets.getInsets(
                android.view.WindowInsets.Type.systemBars() | android.view.WindowInsets.Type.displayCutout());
            view.setPadding(20 + safe.left, 20 + safe.top, 20 + safe.right, 20 + safe.bottom);
            return insets;
        });
        TextView title = text("eiMaths Capture QA", 24);
        root.addView(title);
        root.addView(text("TEST APP / FAKE BOOK / NO SERVER CONNECTION", 12));
        root.addView(text("Android " + Build.VERSION.RELEASE + " / API " + Build.VERSION.SDK_INT, 14));
        root.addView(text("Mode: " + mode(), 19));
        root.addView(text("Try hardware Screenshot and the system Screen recorder.\nOnly OS callbacks count. ADB recording is a separate test.", 14));

        LinearLayout controls = new LinearLayout(this);
        Button window = new Button(this);
        window.setText("Window protection");
        window.setOnClickListener(v -> switchMode(true));
        Button surface = new Button(this);
        surface.setText("Surface protection");
        surface.setOnClickListener(v -> switchMode(false));
        controls.addView(window, new LinearLayout.LayoutParams(0, -2, 1));
        controls.addView(surface, new LinearLayout.LayoutParams(0, -2, 1));
        root.addView(controls);

        status = text("", 17);
        root.addView(status);
        root.addView(text("Protected test area below must be BLACK in captures:", 14));
        SurfaceView book = new SurfaceView(this);
        // Set before setContentView attaches it to the window. Secure in BOTH modes.
        book.setSecure(true);
        book.getHolder().addCallback(new SurfaceHolder.Callback() {
            @Override public void surfaceCreated(SurfaceHolder holder) { drawBook(holder); }
            @Override public void surfaceChanged(SurfaceHolder holder, int f, int w, int h) { drawBook(holder); }
            @Override public void surfaceDestroyed(SurfaceHolder holder) { }
        });
        root.addView(book, new LinearLayout.LayoutParams(-1, 0, 1));

        Button clear = new Button(this);
        clear.setText("Clear diagnostic events");
        clear.setOnClickListener(v -> {
            screenshots = 0;
            recordingStarts = 0;
            prefs.edit().putInt("screenshots", 0).putInt("recordingStarts", 0).putString("history", "").apply();
            refresh();
        });
        root.addView(clear);
        history = text("", 12);
        ScrollView scroll = new ScrollView(this);
        scroll.addView(history);
        root.addView(scroll, new LinearLayout.LayoutParams(-1, 160));
        setContentView(root);
        event("Activity created: " + mode());
    }

    private String mode() { return windowMode ? "WINDOW + SECURE SURFACE" : "SECURE SURFACE ONLY"; }

    private TextView text(String value, int size) {
        TextView view = new TextView(this);
        view.setText(value);
        view.setTextSize(size);
        view.setTextColor(Color.rgb(15, 63, 67));
        view.setPadding(0, 4, 0, 4);
        return view;
    }

    private void switchMode(boolean value) {
        if (windowMode == value) return;
        prefs.edit().putBoolean("windowMode", value).apply();
        recreate();
    }

    private void drawBook(SurfaceHolder holder) {
        Canvas canvas = null;
        try {
            canvas = holder.lockCanvas();
            if (canvas == null) return;
            canvas.drawColor(Color.MAGENTA);
            Paint paint = new Paint(Paint.ANTI_ALIAS_FLAG);
            paint.setColor(Color.BLUE);
            canvas.drawRect(0, 0, canvas.getWidth(), canvas.getHeight() / 2f, paint);
            paint.setColor(Color.WHITE);
            paint.setTextAlign(Paint.Align.CENTER);
            paint.setTextSize(Math.min(50, canvas.getWidth() / 15f));
            canvas.drawText("TEST BOOK CONTENT", canvas.getWidth() / 2f, canvas.getHeight() / 2f - 20, paint);
            canvas.drawText("FAKE PAGE 1 + 2 = 3", canvas.getWidth() / 2f, canvas.getHeight() / 2f + 55, paint);
        } finally {
            if (canvas != null) holder.unlockCanvasAndPost(canvas);
        }
    }

    @Override protected void onStart() {
        super.onStart();
        screenshotCallback = () -> {
            screenshots++;
            prefs.edit().putInt("screenshots", screenshots).apply();
            event("SCREENSHOT callback received: " + mode());
        };
        registerScreenCaptureCallback(getMainExecutor(), screenshotCallback);
        event("Screenshot callback registered");
        if (Build.VERSION.SDK_INT >= 35) {
            recordingCallback = state -> {
                boolean active = state == WindowManager.SCREEN_RECORDING_STATE_VISIBLE;
                if (active && !recording) {
                    recordingStarts++;
                    prefs.edit().putInt("recordingStarts", recordingStarts).apply();
                }
                recording = active;
                event("RECORDING callback: visible=" + active + ": " + mode());
            };
            int initial = getWindowManager().addScreenRecordingCallback(getMainExecutor(), recordingCallback);
            recording = initial == WindowManager.SCREEN_RECORDING_STATE_VISIBLE;
            event("Recording callback registered; initial visible=" + recording);
        }
    }

    @Override protected void onStop() {
        if (screenshotCallback != null) unregisterScreenCaptureCallback(screenshotCallback);
        if (Build.VERSION.SDK_INT >= 35 && recordingCallback != null) {
            getWindowManager().removeScreenRecordingCallback(recordingCallback);
        }
        screenshotCallback = null;
        recordingCallback = null;
        super.onStop();
    }

    private void event(String value) {
        String line = new SimpleDateFormat("HH:mm:ss", Locale.US).format(new Date()) + " " + value;
        Log.i("EiMathsCaptureProbe", line);
        String previous = prefs.getString("history", "");
        String lines = line + "\n" + previous;
        if (lines.length() > 8000) lines = lines.substring(0, 8000);
        prefs.edit().putString("history", lines).apply();
        refresh();
    }

    private void refresh() {
        status.setText("Screenshot callbacks: " + screenshots + "\nRecording starts: " + recordingStarts + " / active: " + recording);
        history.setText(prefs.getString("history", ""));
    }
}
