import React from 'react';

import styles from './index.module.scss';

const Component = () => {
  return (
    <div className={styles.adminMengklikProfilA}>
      <div className={styles.rectangle18}>
        <div className={styles.rectangle6}>
          <p className={styles.berandaProfilAkun}>Beranda → Profil Akun</p>
          <div className={styles.ionNotifcations}>
            <img src="../image/mkmaknum-3kol5uu.svg" className={styles.frame1} />
          </div>
          <p className={styles.admin}>Admin</p>
          <img src="../image/mkmaknum-v30ff0p.svg" className={styles.group} />
        </div>
        <div className={styles.group11}>
          <div className={styles.rectangle16}>
            <p className={styles.profilAkun}>Profil Akun</p>
            <p className={styles.nama}>Nama</p>
            <div className={styles.rectangle10}>
              <p className={styles.ahmadFalihAgussalim}>Ahmad Falih Agussalim</p>
            </div>
            <p className={styles.instansi}>Instansi</p>
            <div className={styles.rectangle19}>
              <p className={styles.ahmadFalihAgussalim}>Telkom</p>
            </div>
            <p className={styles.jabatan}>Jabatan</p>
            <div className={styles.rectangle20}>
              <p className={styles.ahmadFalihAgussalim}>Mahasiswa Kalcer</p>
            </div>
            <p className={styles.role}>Role</p>
            <div className={styles.rectangle21}>
              <p className={styles.ahmadFalihAgussalim}>Admin</p>
            </div>
          </div>
        </div>
      </div>
      <div className={styles.rectangle13}>
        <img
          src="../image/mkmaknuu-22g2le6.png"
          className={styles.logoPertaminaGasNega}
        />
        <div className={styles.autoWrapper}>
          <div className={styles.lineMdHomeTwotone}>
            <img src="../image/mkmaknum-6du7tbr.svg" className={styles.vector} />
            <img src="../image/mkmaknum-zdoz2ek.svg" className={styles.group2} />
            <img
              src="../image/mkmaknum-ozhvw9c.svg"
              className={styles.materialSymbolsHomeR}
            />
          </div>
          <p className={styles.beranda}>Beranda</p>
        </div>
        <div className={styles.autoWrapper3}>
          <img src="../image/mkmaknun-cn9jfcg.svg" className={styles.vector2} />
          <div className={styles.autoWrapper2}>
            <p className={styles.history}>History</p>
            <p className={styles.history2}>History</p>
          </div>
        </div>
        <div className={styles.autoWrapper4}>
          <img src="../image/mkmaknun-n88hcib.svg" className={styles.group3} />
          <p className={styles.managamentUser}>Managament User</p>
        </div>
        <div className={styles.autoWrapper5}>
          <img src="../image/mkmaknun-lmnuqar.svg" className={styles.vector3} />
          <p className={styles.managamentUser}>Integrasi Sistem</p>
        </div>
      </div>
      <p className={styles.beranda2}>Beranda</p>
      <p className={styles.managamentUser2}>Managament User</p>
      <p className={styles.berandaProfilAkun2}>Beranda → Profil Akun</p>
    </div>
  );
}

export default Component;
