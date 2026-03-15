const { createClient } = require('@supabase/supabase-js');
const SUPABASE_URL = 'https://tukkkwtxuaxrbihyammp.supabase.co';
const SUPABASE_ANON_KEY = 'sb_publishable_23puPo1jOwFggf-4YTitRg_BQiGQl9P';

const client1 = createClient(SUPABASE_URL, SUPABASE_ANON_KEY);
const client2 = createClient(SUPABASE_URL, SUPABASE_ANON_KEY);

async function test() {
  const channel1 = client1.channel('app_presence', { config: { presence: { key: 'user1' } } });
  const channel2 = client2.channel('app_presence', { config: { presence: { key: 'user2' } } });

  channel1.on('presence', { event: 'sync' }, () => {
    console.log('C1 Sync:', channel1.presenceState());
  }).subscribe(async (status) => {
    if (status === 'SUBSCRIBED') {
      await channel1.track({ name: 'User 1' });
    }
  });

  setTimeout(() => {
    channel2.on('presence', { event: 'sync' }, () => {
      console.log('C2 Sync:', channel2.presenceState());
    }).subscribe(async (status) => {
      if (status === 'SUBSCRIBED') {
        await channel2.track({ name: 'User 2' });
      }
    });
  }, 1500);
}

test();
