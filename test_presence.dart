import 'package:supabase_flutter/supabase_flutter.dart';
import 'package:flutter/foundation.dart';

class PresenceService {
  static RealtimeChannel? _channel;
  static bool _isTracking = false;

  static Future<void> startTracking() async {
    final supabase = Supabase.instance.client;
    final user = supabase.auth.currentUser;

    if (user == null || _isTracking) return;

    // Use a unique key for presence (the user ID)
    _channel = supabase.channel('app_presence');

    _channel!.subscribe((status, [error]) async {
      if (status == RealtimeSubscribeStatus.subscribed) {
        _isTracking = true;
        
        try {
          // Fetch profile to get real role and name
          final profileRes = await supabase
              .from('profiles')
              .select('full_name, role')
              .eq('id', user.id)
              .maybeSingle();

          final String name = profileRes?['full_name'] ?? user.userMetadata?['full_name'] ?? user.email?.split('@')[0] ?? 'User';
          final String role = profileRes?['role'] ?? 'Citizen';

          await _channel!.track({
            'user_id': user.id,
            'email': user.email,
            'name': name,
            'role': role,
            'status': 'Online',
            'online_at': DateTime.now().toUtc().toIso8601String(),
            'platform': 'Mobile (Citizen App)',
          });
          debugPrint('[Presence] Tracking started for $name ($role)');
        } catch (e) {
          debugPrint('[Presence] Profile fetch/track error: $e');
        }
      }
    });
  }

  static Future<void> stopTracking() async {
    if (_channel != null) {
      try {
        await _channel!.untrack();
        await _channel!.unsubscribe();
      } catch (e) {
        debugPrint('[Presence] Stop tracking error: $e');
      }
      _channel = null;
      _isTracking = false;
      debugPrint('[Presence] Tracking stopped');
    }
  }
}
